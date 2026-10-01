@extends('clinic.layouts.app')

@section('title', $appointment->exists ? 'Reprogramar cita' : 'Agendar cita')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">AGENDA CLÍNICA</span><h1>{{ $appointment->exists ? 'Reprogramar cita' : 'Agendar una cita' }}</h1><p>El sistema evita cruces de horario para un mismo profesional.</p></div><a class="button secondary" href="{{ route('appointments.index') }}">Volver a agenda</a></section>
@if ($patients->isEmpty() || $professionals->isEmpty())<div class="flash flash-error">Para agendar necesitas al menos un paciente activo y un profesional activo. Revisa el directorio antes de continuar.</div>@endif
<form class="form-panel" method="POST" action="{{ $appointment->exists ? route('appointments.update', $appointment) : route('appointments.store') }}">
    @csrf @if ($appointment->exists) @method('PUT') @endif
    <h2 class="form-section-title">Atención</h2>
    <div class="form-grid">
        <div class="field"><label for="patient_id">Paciente *</label><select id="patient_id" name="patient_id" required><option value="">Selecciona un paciente</option>@foreach ($patients as $patient)<option value="{{ $patient->id }}" @selected(old('patient_id', $selectedPatient) == $patient->id)>{{ $patient->full_name }} · {{ $patient->document_number }}</option>@endforeach</select><small class="field-error">{{ $errors->first('patient_id') }}</small></div>
        <div class="field"><label for="professional_id">Profesional *</label><select id="professional_id" name="professional_id" required><option value="">Selecciona un profesional</option>@foreach ($professionals as $professional)<option value="{{ $professional->id }}" @selected(old('professional_id', $appointment->professional_id) == $professional->id)>{{ $professional->full_name }} · {{ $professional->specialty->name }}</option>@endforeach</select><small class="field-error">{{ $errors->first('professional_id') }}</small></div>
        <div class="field"><label for="starts_at">Inicio *</label><input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $appointment->starts_at?->format('Y-m-d\TH:i')) }}" required><small class="field-error">{{ $errors->first('starts_at') }}</small></div>
        <div class="field"><label for="ends_at">Fin *</label><input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $appointment->ends_at?->format('Y-m-d\TH:i')) }}" required><small class="field-error">{{ $errors->first('ends_at') }}</small></div>
        <div class="field span-2"><label for="reason">Motivo de consulta</label><input id="reason" name="reason" value="{{ old('reason', $appointment->reason) }}" maxlength="255" placeholder="Ej. Control general, seguimiento..."></div>
        <div class="field span-2"><label for="notes">Notas administrativas</label><textarea id="notes" name="notes" maxlength="3000" placeholder="Información logística para el equipo (no incluir diagnóstico)">{{ old('notes', $appointment->notes) }}</textarea></div>
    </div>
    <div class="form-actions"><a class="button secondary" href="{{ route('appointments.index') }}">Cancelar</a><button class="button" type="submit" @disabled($patients->isEmpty() || $professionals->isEmpty())>{{ $appointment->exists ? 'Guardar reprogramación' : 'Confirmar cita' }}</button></div>
</form>
@endsection