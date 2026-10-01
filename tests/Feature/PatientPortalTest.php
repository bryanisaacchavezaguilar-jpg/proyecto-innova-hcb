<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientAccessRequest;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_patient_can_only_see_their_review_status(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        PatientAccessRequest::create([
            'user_id' => $user->id,
            'document_type' => 'DNI',
            'document_number' => 'PENDING-1001',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->actingAs($user)->get('/mi-portal')
            ->assertOk()->assertSee('Tu solicitud está en revisión')->assertDontSee('Alergias');
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/patients')->assertForbidden();
        $this->actingAs($user)->get('/mi-portal/citas/solicitar')->assertForbidden();
    }

    public function test_patient_login_redirects_to_the_patient_portal(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('patient.portal'));
    }

    public function test_patient_can_submit_a_complete_access_request(): void
    {
        $registerPage = $this->get('/register')->assertOk();
        $this->assertSame(1, substr_count($registerPage->getContent(), '<form'));
        $registerPage->assertSee('name="document_type"', false)
            ->assertSee('name="date_of_birth"', false);

        $email = 'new-patient@example.test';
        $this->post('/register', [
            'name' => 'Paciente de prueba',
            'email' => $email,
            'document_type' => 'DNI',
            'document_number' => 'TEST-ACCESS-1001',
            'date_of_birth' => '1990-01-01',
            'phone' => '555-0100',
            'password' => 'SecurePassphrase!2026',
            'password_confirmation' => 'SecurePassphrase!2026',
        ])->assertRedirect(route('login'))->assertSessionHasNoErrors();

        $user = User::query()->where('email', $email)->firstOrFail();
        $this->assertSame('patient', $user->role);
        $this->assertDatabaseHas('patient_access_requests', [
            'user_id' => $user->id,
            'document_type' => 'DNI',
            'document_number' => 'TEST-ACCESS-1001',
            'status' => 'pending',
        ]);
    }

    public function test_registration_shows_a_spanish_error_for_an_invalid_email(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Paciente de prueba',
            'email' => 'correo-invalido',
            'document_type' => 'DNI',
            'document_number' => 'TEST-ACCESS-1002',
            'date_of_birth' => '1990-01-01',
            'password' => 'SecurePassphrase!2026',
            'password_confirmation' => 'SecurePassphrase!2026',
        ])->assertRedirect('/register')->assertSessionHasErrors(['email']);

        $this->assertSame('Escribe un correo electrónico válido.', session('errors')->first('email'));
    }

    public function test_admin_can_link_only_a_matching_unlinked_patient_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::create([
            'first_name' => 'Paciente', 'last_name' => 'Verificado', 'document_type' => 'DNI',
            'document_number' => 'MATCH-1001', 'date_of_birth' => '1988-03-09',
        ]);
        $accessRequest = PatientAccessRequest::create([
            'user_id' => $user->id, 'document_type' => 'DNI', 'document_number' => 'MATCH-1001',
            'date_of_birth' => '1988-03-09',
        ]);

        $this->actingAs($admin)->get('/access-requests')->assertOk()->assertSee('Paciente Verificado');
        $this->post(route('access-requests.approve', $accessRequest), ['patient_id' => $patient->id])
            ->assertRedirect(route('access-requests.index'));

        $this->assertSame($patient->id, $user->fresh()->patient_id);
        $this->assertDatabaseHas('patient_access_requests', [
            'id' => $accessRequest->id, 'status' => 'approved', 'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_cannot_link_a_request_to_a_nonmatching_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::create([
            'first_name' => 'Persona', 'last_name' => 'Distinta', 'document_type' => 'DNI',
            'document_number' => 'OTHER-1001', 'date_of_birth' => '1988-03-09',
        ]);
        $accessRequest = PatientAccessRequest::create([
            'user_id' => $user->id, 'document_type' => 'DNI', 'document_number' => 'MATCH-1002',
            'date_of_birth' => '1988-03-09',
        ]);

        $this->actingAs($admin)->post(route('access-requests.approve', $accessRequest), ['patient_id' => $patient->id])
            ->assertUnprocessable();
        $this->assertNull($user->fresh()->patient_id);
    }

    public function test_patient_can_request_a_future_appointment_for_their_own_record(): void
    {
        [$user, $patient, $professional] = $this->linkedPatient();
        $startsAt = now()->addDays(4)->setTime(11, 0);

        $this->actingAs($user)->post('/mi-portal/citas', [
            'professional_id' => $professional->id,
            'starts_at' => $startsAt->format('Y-m-d\TH:i'),
            'reason' => 'Consulta solicitada',
        ])->assertRedirect(route('patient.portal'))->assertSessionHasNoErrors();

        $appointment = Appointment::firstOrFail();
        $this->assertSame($patient->id, $appointment->patient_id);
        $this->assertSame('requested', $appointment->status);
        $this->assertSame('11:30', $appointment->ends_at->format('H:i'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('appointments.status', $appointment), ['status' => 'confirmed'])
            ->assertRedirect();
        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_patient_portal_shows_only_their_appointments_and_cannot_cancel_others(): void
    {
        [$user, $patient, $professional] = $this->linkedPatient();
        $otherPatient = Patient::create([
            'first_name' => 'Otro', 'last_name' => 'Paciente', 'document_type' => 'DNI', 'document_number' => 'PORTAL-OTHER',
        ]);
        $ownAppointment = $this->appointment($patient, $professional, 'Cita de mi expediente');
        $otherAppointment = $this->appointment($otherPatient, $professional, 'Cita de otro expediente');

        $this->actingAs($user)->get('/mi-portal')
            ->assertOk()->assertSee('Cita de mi expediente')->assertDontSee('Cita de otro expediente');
        $this->delete(route('patient.appointments.cancel', $otherAppointment))->assertNotFound();
        $this->assertSame('requested', $ownAppointment->fresh()->status);
        $this->assertSame('requested', $otherAppointment->fresh()->status);
    }

    private function linkedPatient(): array
    {
        $patient = Patient::create([
            'first_name' => 'Paciente', 'last_name' => 'Portal', 'document_type' => 'DNI', 'document_number' => 'PORTAL-1001',
            'allergies' => 'Dato clínico que no debe aparecer',
        ]);
        $user = User::factory()->create(['role' => 'patient', 'patient_id' => $patient->id]);
        $specialty = Specialty::create(['name' => 'Consulta portal']);
        $professional = Professional::create(['specialty_id' => $specialty->id, 'full_name' => 'Profesional portal']);

        return [$user, $patient, $professional];
    }

    private function appointment(Patient $patient, Professional $professional, string $reason): Appointment
    {
        $startsAt = now()->addDays(6)->setTime(9, 0);

        return Appointment::create([
            'patient_id' => $patient->id,
            'professional_id' => $professional->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(30),
            'status' => 'requested',
            'reason' => $reason,
        ]);
    }
}