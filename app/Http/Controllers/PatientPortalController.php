<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Professional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user()->load(['patient', 'patientAccessRequest']);

        if (! $user->patient) {
            return view('patient.portal.pending', ['accessRequest' => $user->patientAccessRequest]);
        }

        abort_unless($user->patient->is_active, 403);

        $appointments = $user->patient->appointments()
            ->with('professional.specialty')
            ->whereIn('status', ['requested', 'scheduled', 'confirmed', 'in_progress'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        return view('patient.portal.dashboard', compact('user', 'appointments'));
    }

    public function createAppointment(Request $request): View|RedirectResponse
    {
        $patient = $request->user()->patient;
        abort_unless($patient && $patient->is_active, 403);

        $professionals = Professional::with('specialty')
            ->where('is_active', true)
            ->whereHas('specialty', fn ($query) => $query->where('is_active', true))
            ->orderBy('full_name')
            ->get();

        return view('patient.portal.appointment-form', compact('professionals'));
    }

    public function storeAppointment(Request $request): RedirectResponse
    {
        $patient = $request->user()->patient;
        abort_unless($patient && $patient->is_active, 403);

        $data = $request->validate([
            'professional_id' => ['required', 'exists:professionals,id,is_active,1'],
            'starts_at' => ['required', 'date', 'after:now'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes(30);

        $appointment = DB::transaction(function () use ($data, $patient, $request, $startsAt, $endsAt) {
            Professional::query()->whereKey($data['professional_id'])->lockForUpdate()->firstOrFail();

            $conflict = Appointment::where('professional_id', $data['professional_id'])
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->exists();

            if ($conflict) {
                return null;
            }

            return Appointment::create([
                'patient_id' => $patient->id,
                'professional_id' => $data['professional_id'],
                'created_by' => $request->user()->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'requested',
                'reason' => $data['reason'] ?? null,
            ]);
        });

        if (! $appointment) {
            return back()->withInput()->withErrors(['starts_at' => 'Ese horario ya no está disponible. Elige otro.']);
        }

        return redirect()->route('patient.portal')->with('success', 'Solicitud enviada. La clínica confirmará tu cita.');
    }

    public function cancelAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->patient_id && $appointment->patient_id === $request->user()->patient_id, 404);
        abort_unless($appointment->status === 'requested' && $appointment->starts_at->isFuture(), 422);

        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('patient.portal')->with('success', 'Solicitud de cita cancelada.');
    }
}