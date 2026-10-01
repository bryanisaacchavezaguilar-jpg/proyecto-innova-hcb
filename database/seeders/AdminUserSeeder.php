<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('app.admin.password');

        if (! app()->environment('local') || ! is_string($password) || $password === '') {
            return;
        }

        User::updateOrCreate(
            ['email' => config('app.admin.email')],
            [
                'name' => 'Administrador HCB',
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
    }
}