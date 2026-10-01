<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_pages_require_an_internal_account(): void
    {
        $this->get('/patients')->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'doctor']);
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/patients/create')->assertForbidden();
    }

    public function test_receptionist_can_register_a_patient(): void
    {
        $user = User::factory()->create(['role' => 'receptionist']);

        $response = $this->actingAs($user)->post('/patients', [
            'first_name' => 'María',
            'last_name' => 'Prueba',
            'document_type' => 'DNI',
            'document_number' => 'TEST-9001',
            'phone' => '555-0100',
            'allergies' => 'Este campo no lo debe guardar recepción',
        ]);

        $patient = Patient::where('document_number', 'TEST-9001')->firstOrFail();
        $response->assertRedirect(route('patients.show', $patient));
        $this->assertSame($user->id, $patient->created_by);
        $this->assertNull($patient->allergies);
    }

    public function test_reception_cannot_read_clinical_notes_or_patient_history(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $patient = Patient::create([
            'first_name' => 'Caso', 'last_name' => 'Confidencial', 'document_type' => 'DNI',
            'document_number' => 'PRIVACY-1', 'allergies' => 'Dato clínico privado', 'medical_history' => 'Antecedente privado',
        ]);

        $this->actingAs($receptionist)->get(route('patients.show', $patient))
            ->assertOk()->assertDontSee('Dato clínico privado')->assertDontSee('Antecedente privado');
    }

    public function test_appointments_cannot_overlap_for_the_same_professional(): void
    {
        [$user, $patient, $professional] = $this->clinicRecords();
        $this->actingAs($user);
        $start = now()->addDays(3)->setTime(10, 0);
        $payload = [
            'patient_id' => $patient->id,
            'professional_id' => $professional->id,
            'starts_at' => $start->format('Y-m-d\TH:i'),
            'ends_at' => $start->copy()->addMinutes(30)->format('Y-m-d\TH:i'),
            'reason' => 'Control general',
        ];

        $this->post('/appointments', $payload)->assertSessionHasNoErrors();
        $appointment = Appointment::firstOrFail();
        $this->get(route('appointments.edit', $appointment))->assertOk();
        $rescheduledStart = $start->copy()->addHour();
        $payload['starts_at'] = $rescheduledStart->format('Y-m-d\TH:i');
        $payload['ends_at'] = $rescheduledStart->copy()->addMinutes(30)->format('Y-m-d\TH:i');
        $this->put(route('appointments.update', $appointment), $payload)->assertSessionHasNoErrors();
        $this->assertSame('11:00', $appointment->fresh()->starts_at->format('H:i'));

        $payload['patient_id'] = Patient::create([
            'first_name' => 'Otra', 'last_name' => 'Persona', 'document_type' => 'DNI', 'document_number' => 'TEST-9002',
        ])->id;
        $payload['starts_at'] = $rescheduledStart->copy()->addMinutes(15)->format('Y-m-d\TH:i');
        $payload['ends_at'] = $rescheduledStart->copy()->addMinutes(45)->format('Y-m-d\TH:i');

        $this->from('/appointments/create')->post('/appointments', $payload)->assertSessionHasErrors('starts_at');
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_consultation_and_prescriptions_are_saved_as_one_clinical_record(): void
    {
        [$user, $patient, $professional] = $this->clinicRecords();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'professional_id' => $professional->id,
            'created_by' => $user->id,
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addMinutes(25),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->post(route('consultations.store', $appointment), [
            'chief_complaint' => 'Dolor de cabeza',
            'diagnosis' => 'Evaluación de rutina',
            'blood_pressure' => '120/80',
            'weight_kg' => 62.5,
            'prescriptions' => [[
                'medication' => 'Medicamento de prueba',
                'dosage' => '500 mg',
                'frequency' => 'Cada 8 horas',
                'duration' => '3 días',
                'instructions' => 'Tomar con alimentos',
            ]],
        ]);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);
        $this->assertDatabaseHas('consultations', ['appointment_id' => $appointment->id, 'diagnosis' => 'Evaluación de rutina']);
        $this->assertDatabaseHas('prescriptions', ['medication' => 'Medicamento de prueba', 'dosage' => '500 mg']);
        $response->assertRedirect(route('consultations.show', $appointment->consultation));
    }

    public function test_doctors_only_see_patients_assigned_to_their_professional_profile(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $specialty = Specialty::create(['name' => 'Medicina asignada']);
        $professional = Professional::create([
            'user_id' => $doctor->id,
            'specialty_id' => $specialty->id,
            'full_name' => 'Doctora asignada',
        ]);
        $assignedPatient = Patient::create([
            'first_name' => 'Paciente', 'last_name' => 'Asignado', 'document_type' => 'DNI', 'document_number' => 'SCOPE-1',
        ]);
        $otherPatient = Patient::create([
            'first_name' => 'Paciente', 'last_name' => 'Privado', 'document_type' => 'DNI', 'document_number' => 'SCOPE-2',
        ]);
        Appointment::create([
            'patient_id' => $assignedPatient->id, 'professional_id' => $professional->id,
            'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addMinutes(30),
        ]);

        $response = $this->actingAs($doctor)->get('/patients');
        $response->assertOk()->assertSee('Paciente Asignado')->assertDontSee('Paciente Privado');
        $this->get(route('patients.show', $otherPatient))->assertForbidden();
    }

    public function test_admin_can_add_a_professional_and_link_a_doctor_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $specialty = Specialty::create(['name' => 'Cardiología']);

        $response = $this->actingAs($admin)->post('/professionals', [
            'full_name' => 'Dra. Cuenta Vinculada',
            'specialty_id' => $specialty->id,
            'user_id' => $doctor->id,
            'license_number' => 'TEST-LIC-2',
        ]);

        $response->assertRedirect(route('professionals.index'));
        $this->assertDatabaseHas('professionals', [
            'user_id' => $doctor->id,
            'full_name' => 'Dra. Cuenta Vinculada',
            'license_number' => 'TEST-LIC-2',
        ]);
    }

    private function clinicRecords(): array
    {
        $user = User::factory()->create(['role' => 'admin']);
        $specialty = Specialty::create(['name' => 'Medicina de prueba']);
        $professional = Professional::create([
            'specialty_id' => $specialty->id,
            'full_name' => 'Profesional de prueba',
            'license_number' => 'TEST-LIC-1',
        ]);
        $patient = Patient::create([
            'first_name' => 'Paciente',
            'last_name' => 'de prueba',
            'document_type' => 'DNI',
            'document_number' => 'TEST-9000',
        ]);

        return [$user, $patient, $professional];
    }
}