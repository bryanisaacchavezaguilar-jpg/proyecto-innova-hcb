<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientAccessRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientAccessController extends Controller
{
    public function index(): View
    {
        $requests = PatientAccessRequest::with('user')
            ->where('status', 'pending')
            ->oldest()
            ->get()
            ->map(function (PatientAccessRequest $accessRequest) {
                $candidates = Patient::query()
                    ->where('document_type', $accessRequest->document_type)
                    ->where('document_number', $accessRequest->document_number)
                    ->when($accessRequest->date_of_birth, fn ($query) => $query->whereDate('date_of_birth', $accessRequest->date_of_birth))
                    ->where('is_active', true)
                    ->whereDoesntHave('portalAccount')
                    ->get();

                return ['request' => $accessRequest, 'candidates' => $candidates];
            });

        return view('clinic.access-requests.index', compact('requests'));
    }

    public function approve(Request $request, PatientAccessRequest $accessRequest): RedirectResponse
    {
        abort_unless($accessRequest->status === 'pending', 422);

        $data = $request->validate(['patient_id' => ['required', 'exists:patients,id']]);
        $patient = Patient::findOrFail($data['patient_id']);

        abort_unless($patient->is_active, 422, 'Solo se puede vincular un expediente activo.');
        abort_unless(
            $patient->document_type === $accessRequest->document_type
                && $patient->document_number === $accessRequest->document_number
                && (! $accessRequest->date_of_birth || $patient->date_of_birth?->toDateString() === $accessRequest->date_of_birth->toDateString()),
            422,
            'Los datos de identidad no coinciden con el expediente seleccionado.',
        );

        DB::transaction(function () use ($request, $accessRequest, $patient) {
            $lockedPatient = Patient::query()->whereKey($patient->id)->lockForUpdate()->firstOrFail();
            $patientUser = User::query()->where('patient_id', $lockedPatient->id)->lockForUpdate()->first();
            abort_if($patientUser && $patientUser->id !== $accessRequest->user_id, 409, 'Este expediente ya tiene una cuenta vinculada.');

            $user = User::query()->whereKey($accessRequest->user_id)->lockForUpdate()->firstOrFail();
            $user->update(['patient_id' => $lockedPatient->id]);
            $accessRequest->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()->route('access-requests.index')->with('success', 'Identidad revisada y acceso vinculado al expediente.');
    }
}