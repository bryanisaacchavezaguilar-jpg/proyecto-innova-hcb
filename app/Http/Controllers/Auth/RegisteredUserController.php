<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\PatientAccessRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'document_type' => ['required', 'string', Rule::in(['DNI', 'CC', 'CE', 'Pasaporte', 'Otro'])],
            'document_number' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ], [
            'email.required' => 'Escribe tu correo electrónico.',
            'email.lowercase' => 'Escribe el correo electrónico en minúsculas.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'email.unique' => 'Este correo ya tiene una cuenta. Inicia sesión o usa otro.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->string('name')->trim()->toString(),
                'email' => mb_strtolower($request->string('email')->trim()->toString()),
                'password' => $request->string('password')->toString(),
                'role' => 'patient',
            ]);

            PatientAccessRequest::create([
                'user_id' => $user->id,
                'document_type' => $request->string('document_type')->toString(),
                'document_number' => $request->string('document_number')->trim()->toString(),
                'date_of_birth' => $request->date_of_birth,
                'phone' => $request->input('phone'),
            ]);

            return $user;
        });

        return redirect()->route('login')->with('status', 'Solicitud creada. El equipo de HCB revisará los datos y habilitará el acceso cuando verifique tu identidad.');
    }
}
