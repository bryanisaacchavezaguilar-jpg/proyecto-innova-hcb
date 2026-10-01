<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function dashboard(): View
    {
        $today = today();
        $appointmentQuery = $this->appointmentsFor(request()->user());
        $appointments = (clone $appointmentQuery)->with(['patient', 'professional.specialty'])
            ->whereDate('starts_at', $today)
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        return view('clinic.dashboard', [
            'appointments' => $appointments,
            'todayCount' => (clone $appointmentQuery)->whereDate('starts_at', $today)->count(),
            'waitingCount' => (clone $appointmentQuery)->whereDate('starts_at', $today)->whereIn('status', ['requested', 'scheduled', 'confirmed'])->count(),
            'patientCount' => Patient::query()
                ->when(request()->user()->role === 'doctor', fn ($query) => $query->whereHas('appointments', fn ($appointments) => $appointments->where('professional_id', $this->professionalId(request()->user()))))
                ->where('is_active', true)->count(),
            'professionalCount' => Professional::where('is_active', true)->count(),
            'recentPatients' => Patient::query()
                ->when(request()->user()->role === 'doctor', fn ($query) => $query->whereHas('appointments', fn ($appointments) => $appointments->where('professional_id', $this->professionalId(request()->user()))))
                ->latest()->limit(5)->get(),
        ]);
    }

    public function patients(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $patients = Patient::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            }))
            ->when($request->user()->role === 'doctor', fn ($query) => $query->whereHas('appointments', fn ($appointments) => $appointments->where('professional_id', $this->professionalId($request->user()))))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(12)
            ->withQueryString();

        return view('clinic.patients.index', compact('patients', 'search'));
    }

    public function createPatient(): View
    {
        return view('clinic.patients.form', ['patient' => new Patient]);
    }

    public function storePatient(Request $request): RedirectResponse
    {
        $data = $this->patientData($request);
        $data['created_by'] = $request->user()->id;
        $patient = Patient::create($data);

        return redirect()->route('patients.show', $patient)->with('success', 'Paciente registrado correctamente.');
    }

    public function showPatient(Patient $patient): View
    {
        abort_unless($this->canViewPatient(request()->user(), $patient), 403);

        $relations = [
            'appointments' => fn ($query) => $query->when(request()->user()->role === 'doctor', fn ($query) => $query->where('professional_id', $this->professionalId(request()->user())))->with('professional.specialty')->latest('starts_at')->limit(10),
        ];
        if (request()->user()->role !== 'receptionist') {
            $relations['consultations'] = fn ($query) => $query->when(request()->user()->role === 'doctor', fn ($query) => $query->where('professional_id', $this->professionalId(request()->user())))->with(['professional', 'prescriptions'])->latest('consultation_date')->limit(10);
        }
        $patient->load($relations);

        return view('clinic.patients.show', compact('patient'));
    }

    public function editPatient(Patient $patient): View
    {
        return view('clinic.patients.form', compact('patient'));
    }

    public function updatePatient(Request $request, Patient $patient): RedirectResponse
    {
        $patient->update($this->patientData($request, $patient));

        return redirect()->route('patients.show', $patient)->with('success', 'Expediente actualizado.');
    }

    public function professionals(): View
    {
        $professionals = Professional::with('specialty')->orderByDesc('is_active')->orderBy('full_name')->get();

        return view('clinic.professionals.index', compact('professionals'));
    }

    public function createProfessional(): View
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $doctorUsers = User::where('role', 'doctor')->whereDoesntHave('professional')->orderBy('name')->get();

        return view('clinic.professionals.form', compact('specialties', 'doctorUsers'));
    }

    public function storeProfessional(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'specialty_id' => ['required', 'exists:specialties,id,is_active,1'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'doctor')), Rule::unique('professionals', 'user_id')],
            'license_number' => ['nullable', 'string', 'max:100', Rule::unique('professionals', 'license_number')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        Professional::create($data);

        return redirect()->route('professionals.index')->with('success', 'Profesional agregado al equipo clínico.');
    }

    public function toggleProfessionalStatus(Professional $professional): RedirectResponse
    {
        $professional->update(['is_active' => ! $professional->is_active]);

        return back()->with('success', 'Disponibilidad del profesional actualizada.');
    }

    public function appointments(Request $request): View
    {
        $date = $request->query('date', today()->toDateString());
        $appointments = $this->appointmentsFor($request->user())->with(['patient', 'professional.specialty', 'consultation'])
            ->whereDate('starts_at', $date)
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('clinic.appointments.index', compact('appointments', 'date'));
    }

    public function createAppointment(Request $request): View
    {
        $patients = Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get();
        $professionals = Professional::with('specialty')->where('is_active', true)->orderBy('full_name')->get();
        $selectedPatient = $request->integer('patient');
        $appointment = new Appointment;

        return view('clinic.appointments.form', compact('patients', 'professionals', 'selectedPatient', 'appointment'));
    }

    public function storeAppointment(Request $request): RedirectResponse
    {
        $data = $this->appointmentData($request);
        $data['created_by'] = $request->user()->id;
        $appointment = $this->saveAppointment($data);

        if (! $appointment) {
            return back()->withInput()->withErrors(['starts_at' => 'El profesional ya tiene una cita que se cruza con ese horario.']);
        }

        return redirect()->route('appointments.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('success', 'Cita agendada correctamente.');
    }

    public function editAppointment(Appointment $appointment): View
    {
        abort_if(in_array($appointment->status, ['completed', 'cancelled', 'no_show'], true), 422, 'Esta cita ya no se puede reprogramar.');

        $patients = Patient::where('is_active', true)->orWhere('id', $appointment->patient_id)->orderBy('last_name')->orderBy('first_name')->get();
        $professionals = Professional::with('specialty')->where('is_active', true)->orderBy('full_name')->get();
        $selectedPatient = $appointment->patient_id;

        return view('clinic.appointments.form', compact('patients', 'professionals', 'selectedPatient', 'appointment'));
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_if(in_array($appointment->status, ['completed', 'cancelled', 'no_show'], true), 422, 'Esta cita ya no se puede reprogramar.');

        $savedAppointment = $this->saveAppointment($this->appointmentData($request), $appointment);

        if (! $savedAppointment) {
            return back()->withInput()->withErrors(['starts_at' => 'El profesional ya tiene una cita que se cruza con ese horario.']);
        }

        return redirect()->route('appointments.index', ['date' => $savedAppointment->starts_at->toDateString()])
            ->with('success', 'La cita fue reprogramada correctamente.');
    }

    public function updateAppointmentStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['requested', 'scheduled', 'confirmed', 'in_progress', 'cancelled', 'no_show'])],
        ]);

        $appointment->update($data);

        return back()->with('success', 'Estado de la cita actualizado.');
    }

    public function createConsultation(Appointment $appointment): View|RedirectResponse
    {
        $this->authorizeProfessionalAppointment(request()->user(), $appointment);
        $appointment->load(['patient', 'professional.specialty', 'consultation']);

        if ($appointment->consultation) {
            return redirect()->route('consultations.show', $appointment->consultation);
        }

        abort_if($appointment->status === 'cancelled', 422, 'No se puede registrar una consulta para una cita cancelada.');

        return view('clinic.consultations.form', compact('appointment'));
    }

    public function storeConsultation(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeProfessionalAppointment($request->user(), $appointment);
        abort_if($appointment->status === 'cancelled', 422, 'No se puede registrar una consulta para una cita cancelada.');
        abort_if($appointment->consultation()->exists(), 409, 'Esta cita ya tiene una consulta registrada.');

        $data = $request->validate([
            'chief_complaint' => ['nullable', 'string', 'max:255'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'clinical_notes' => ['nullable', 'string', 'max:10000'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'temperature' => ['nullable', 'numeric', 'between:25,45'],
            'weight_kg' => ['nullable', 'numeric', 'between:0.1,500'],
            'height_cm' => ['nullable', 'numeric', 'between:20,250'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'prescriptions' => ['nullable', 'array', 'max:20'],
            'prescriptions.*.medication' => ['required_with:prescriptions', 'string', 'max:255'],
            'prescriptions.*.dosage' => ['required_with:prescriptions', 'string', 'max:100'],
            'prescriptions.*.frequency' => ['required_with:prescriptions', 'string', 'max:100'],
            'prescriptions.*.duration' => ['nullable', 'string', 'max:100'],
            'prescriptions.*.instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $consultation = DB::transaction(function () use ($data, $appointment, $request) {
            $consultation = $appointment->consultation()->create([
                ...collect($data)->except('prescriptions')->all(),
                'patient_id' => $appointment->patient_id,
                'professional_id' => $appointment->professional_id,
                'created_by' => $request->user()->id,
                'consultation_date' => now(),
            ]);

            foreach ($data['prescriptions'] ?? [] as $prescription) {
                $consultation->prescriptions()->create($prescription);
            }

            $appointment->update(['status' => 'completed']);

            return $consultation;
        });

        return redirect()->route('consultations.show', $consultation)->with('success', 'Consulta y receta registradas.');
    }

    public function showConsultation(Consultation $consultation): View
    {
        abort_unless(request()->user()->role !== 'doctor' || $consultation->professional_id === $this->professionalId(request()->user()), 403);
        $consultation->load(['patient', 'professional.specialty', 'prescriptions']);

        return view('clinic.consultations.show', compact('consultation'));
    }

    private function patientData(Request $request, ?Patient $patient = null): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => ['required', 'string', 'max:50', Rule::unique('patients', 'document_number')->ignore($patient)],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
        if ($request->user()->role !== 'receptionist') {
            $rules['allergies'] = ['nullable', 'string', 'max:5000'];
            $rules['medical_history'] = ['nullable', 'string', 'max:10000'];
        }

        return $request->validate($rules);
    }

    private function appointmentData(Request $request): array
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id,is_active,1'],
            'professional_id' => ['required', 'exists:professionals,id,is_active,1'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['starts_at'] = Carbon::parse($data['starts_at']);
        $data['ends_at'] = Carbon::parse($data['ends_at']);

        return $data;
    }

    private function saveAppointment(array $data, ?Appointment $appointment = null): ?Appointment
    {
        return DB::transaction(function () use ($data, $appointment) {
            Professional::query()->whereKey($data['professional_id'])->lockForUpdate()->firstOrFail();

            $conflict = Appointment::where('professional_id', $data['professional_id'])
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $data['ends_at'])
                ->where('ends_at', '>', $data['starts_at'])
                ->when($appointment, fn ($query) => $query->whereKeyNot($appointment->id))
                ->exists();

            if ($conflict) {
                return null;
            }

            if ($appointment) {
                $appointment->update($data);

                return $appointment;
            }

            return Appointment::create($data);
        });
    }

    private function appointmentsFor(User $user): Builder
    {
        return Appointment::query()->when(
            $user->role === 'doctor',
            fn ($query) => $query->where('professional_id', $this->professionalId($user)),
        );
    }

    private function professionalId(User $user): int
    {
        return (int) ($user->professional?->id ?? 0);
    }

    private function canViewPatient(User $user, Patient $patient): bool
    {
        return $user->role !== 'doctor' || $patient->appointments()->where('professional_id', $this->professionalId($user))->exists();
    }

    private function authorizeProfessionalAppointment(User $user, Appointment $appointment): void
    {
        abort_unless($user->role !== 'doctor' || $appointment->professional_id === $this->professionalId($user), 403);
    }
}