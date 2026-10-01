<?php

namespace Tests\Feature\Auth;

use App\Models\PatientAccessRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk()->assertSee('Solicitar acceso');
    }

    public function test_public_registration_creates_a_patient_request_without_linking_a_record(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'patient@example.com',
            'document_type' => 'DNI',
            'document_number' => 'PATIENT-9001',
            'date_of_birth' => '1990-05-14',
            'password' => 'secure-patient-password',
            'password_confirmation' => 'secure-patient-password',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHas('status');
        $this->assertGuest();

        $user = User::where('email', 'patient@example.com')->firstOrFail();
        $this->assertSame('patient', $user->role);
        $this->assertNull($user->patient_id);
        $this->assertDatabaseHas('patient_access_requests', [
            'user_id' => $user->id,
            'document_number' => 'PATIENT-9001',
            'status' => 'pending',
        ]);
    }

    public function test_patient_registration_rejects_short_passwords(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'patient@example.com',
            'document_type' => 'DNI',
            'document_number' => 'PATIENT-9002',
            'date_of_birth' => '1990-05-14',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }
}
