@extends('clinic.layouts.app')

@section('title', 'Registrar consulta')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">NOTA CLÍNICA · {{ $appointment->starts_at->format('d/m/Y H:i') }}</span><h1>Registrar atención</h1><p>{{ $appointment->patient->full_name }} · {{ $appointment->professional->full_name }}</p></div><a class="button secondary" href="{{ route('appointments.index', ['date' => $appointment->starts_at->toDateString()]) }}">Volver a agenda</a></section>
<form class="form-panel" method="POST" action="{{ route('consultations.store', $appointment) }}">
    @csrf
    <div class="alert-note"><strong>Paciente:</strong> {{ $appointment->patient->full_name }} · {{ $appointment->patient->document_type }} {{ $appointment->patient->document_number }}<br><strong>Alergias:</strong> {{ $appointment->patient->allergies ?: 'No registradas' }}</div>
    <h2 class="form-section-title">Evaluación</h2>
    <div class="form-grid">
        <div class="field span-2"><label for="chief_complaint">Motivo de consulta</label><input id="chief_complaint" name="chief_complaint" value="{{ old('chief_complaint', $appointment->reason) }}" maxlength="255"></div>
        <div class="field"><label for="diagnosis">Impresión diagnóstica</label><textarea id="diagnosis" name="diagnosis" maxlength="5000">{{ old('diagnosis') }}</textarea></div>
        <div class="field"><label for="treatment_plan">Plan de tratamiento</label><textarea id="treatment_plan" name="treatment_plan" maxlength="5000">{{ old('treatment_plan') }}</textarea></div>
        <div class="field span-2"><label for="clinical_notes">Notas de evolución</label><textarea id="clinical_notes" name="clinical_notes" maxlength="10000">{{ old('clinical_notes') }}</textarea></div>
    </div>
    <h2 class="form-section-title">Signos vitales y seguimiento</h2>
    <div class="form-grid three">
        <div class="field"><label for="blood_pressure">Presión arterial</label><input id="blood_pressure" name="blood_pressure" value="{{ old('blood_pressure') }}" maxlength="20" placeholder="120/80 mmHg"></div>
        <div class="field"><label for="temperature">Temperatura (°C)</label><input id="temperature" type="number" step="0.1" min="25" max="45" name="temperature" value="{{ old('temperature') }}"></div>
        <div class="field"><label for="weight_kg">Peso (kg)</label><input id="weight_kg" type="number" step="0.01" min="0.1" max="500" name="weight_kg" value="{{ old('weight_kg') }}"></div>
        <div class="field"><label for="height_cm">Estatura (cm)</label><input id="height_cm" type="number" step="0.01" min="20" max="250" name="height_cm" value="{{ old('height_cm') }}"></div>
        <div class="field"><label for="follow_up_date">Fecha de seguimiento</label><input id="follow_up_date" type="date" min="{{ today()->toDateString() }}" name="follow_up_date" value="{{ old('follow_up_date') }}"></div>
    </div>
    <h2 class="form-section-title">Prescripción</h2>
    <p class="panel-subtitle">Añade los medicamentos indicados; cada prescripción quedará asociada a esta consulta.</p>
    <div id="medication-list"></div>
    <button class="button secondary small" type="button" id="add-medication" style="margin-top:10px">+ Añadir medicamento</button>
    <template id="medication-template"><div class="medication-row"><div class="field"><label>Medicamento</label><input name="prescriptions[INDEX][medication]" maxlength="255" required></div><div class="field"><label>Dosis</label><input name="prescriptions[INDEX][dosage]" maxlength="100" placeholder="500 mg" required></div><div class="field"><label>Frecuencia</label><input name="prescriptions[INDEX][frequency]" maxlength="100" placeholder="Cada 8 horas" required></div><div class="field"><label>Duración</label><input name="prescriptions[INDEX][duration]" maxlength="100" placeholder="5 días"></div><button class="button danger small remove-medication" type="button" aria-label="Quitar medicamento">Quitar</button><div class="field span-2"><label>Indicaciones adicionales</label><input name="prescriptions[INDEX][instructions]" maxlength="1000" placeholder="Con alimentos, vía de administración..."></div></div></template>
    <div class="form-actions"><a class="button secondary" href="{{ route('appointments.index', ['date' => $appointment->starts_at->toDateString()]) }}">Cancelar</a><button class="button" type="submit">Guardar consulta</button></div>
</form>
<script>
    let medicationIndex = 0;
    document.getElementById('add-medication').addEventListener('click', () => {
        const template = document.getElementById('medication-template').innerHTML.replaceAll('INDEX', medicationIndex++);
        document.getElementById('medication-list').insertAdjacentHTML('beforeend', template);
    });
    document.getElementById('medication-list').addEventListener('click', event => {
        if (event.target.matches('.remove-medication')) event.target.closest('.medication-row').remove();
    });
</script>
@endsection