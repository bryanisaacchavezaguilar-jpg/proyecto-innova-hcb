<?php

use App\Http\Controllers\ClinicController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PatientAccessController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? (Auth::user()->role === 'patient' ? 'patient.portal' : 'dashboard') : 'login'));

Route::middleware(['auth', 'role:patient'])->prefix('mi-portal')->name('patient.')->group(function () {
    Route::get('/', [PatientPortalController::class, 'dashboard'])->name('portal');
    Route::get('/citas/solicitar', [PatientPortalController::class, 'createAppointment'])->name('appointments.create');
    Route::post('/citas', [PatientPortalController::class, 'storeAppointment'])->middleware('throttle:10,1')->name('appointments.store');
    Route::delete('/citas/{appointment}', [PatientPortalController::class, 'cancelAppointment'])->name('appointments.cancel');
});

Route::middleware(['auth', 'can:access-clinic'])->group(function () {
    Route::get('/dashboard', [ClinicController::class, 'dashboard'])->name('dashboard');

    Route::get('/patients', [ClinicController::class, 'patients'])->name('patients.index');
    Route::get('/patients/create', [ClinicController::class, 'createPatient'])->middleware('role:admin,receptionist,nurse')->name('patients.create');
    Route::post('/patients', [ClinicController::class, 'storePatient'])->middleware('role:admin,receptionist,nurse')->name('patients.store');
    Route::get('/patients/{patient}', [ClinicController::class, 'showPatient'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [ClinicController::class, 'editPatient'])->middleware('role:admin,receptionist,nurse')->name('patients.edit');
    Route::put('/patients/{patient}', [ClinicController::class, 'updatePatient'])->middleware('role:admin,receptionist,nurse')->name('patients.update');

    Route::get('/professionals', [ClinicController::class, 'professionals'])->name('professionals.index');
    Route::get('/professionals/create', [ClinicController::class, 'createProfessional'])->middleware('role:admin')->name('professionals.create');
    Route::post('/professionals', [ClinicController::class, 'storeProfessional'])->middleware('role:admin')->name('professionals.store');
    Route::patch('/professionals/{professional}/status', [ClinicController::class, 'toggleProfessionalStatus'])->middleware('role:admin')->name('professionals.status');

    Route::get('/access-requests', [PatientAccessController::class, 'index'])->middleware('role:admin')->name('access-requests.index');
    Route::post('/access-requests/{accessRequest}/approve', [PatientAccessController::class, 'approve'])->middleware('role:admin')->name('access-requests.approve');

    Route::get('/appointments', [ClinicController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/create', [ClinicController::class, 'createAppointment'])->middleware('role:admin,receptionist,nurse')->name('appointments.create');
    Route::post('/appointments', [ClinicController::class, 'storeAppointment'])->middleware('role:admin,receptionist,nurse')->name('appointments.store');
    Route::get('/appointments/{appointment}/edit', [ClinicController::class, 'editAppointment'])->middleware('role:admin,receptionist,nurse')->name('appointments.edit');
    Route::put('/appointments/{appointment}', [ClinicController::class, 'updateAppointment'])->middleware('role:admin,receptionist,nurse')->name('appointments.update');
    Route::patch('/appointments/{appointment}/status', [ClinicController::class, 'updateAppointmentStatus'])->middleware('role:admin,receptionist,nurse')->name('appointments.status');

    Route::get('/appointments/{appointment}/consultation', [ClinicController::class, 'createConsultation'])->middleware('role:admin,doctor,nurse')->name('consultations.create');
    Route::post('/appointments/{appointment}/consultation', [ClinicController::class, 'storeConsultation'])->middleware('role:admin,doctor,nurse')->name('consultations.store');
    Route::get('/consultations/{consultation}', [ClinicController::class, 'showConsultation'])->middleware('role:admin,doctor,nurse')->name('consultations.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('role:admin')->name('profile.destroy');
});

require __DIR__.'/auth.php';
