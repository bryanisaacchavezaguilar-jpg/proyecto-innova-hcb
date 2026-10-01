<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateClinicUser extends Command
{
    protected $signature = 'clinic:user';

    protected $description = 'Crea una cuenta interna para el personal de HCB Salud';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre completo'));
        $email = mb_strtolower(trim((string) $this->ask('Correo institucional')));

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
            $this->error('Nombre requerido y correo válido no registrado.');

            return self::FAILURE;
        }

        $defaultRole = User::where('role', 'admin')->exists() ? 'receptionist' : 'admin';
        $role = $this->choice('Rol de acceso', ['admin', 'doctor', 'nurse', 'receptionist'], $defaultRole);
        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmation = (string) $this->secret('Confirma la contraseña');

        if (mb_strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
            $this->error('La contraseña debe tener al menos 12 caracteres y coincidir con su confirmación.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'email_verified_at' => now(),
        ]);

        $this->info("Cuenta {$role} creada para {$email}.");

        return self::SUCCESS;
    }
}