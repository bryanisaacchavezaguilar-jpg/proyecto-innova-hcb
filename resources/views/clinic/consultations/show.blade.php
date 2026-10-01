@extends('clinic.layouts.app')

@section('title', 'Consulta clínica')

@section('content')
<section class="profile-head"><div><span class="eyebrow-light">CONSULTA · {{ $consultation->consultation_date->format('d/m/Y H:i') }}</span><div class="profile-name">{{ $consultation->patient->full_name }}</div><div class="profile-doc">{{ $consultation->professional->full_name }} · {{ $consultation->professional->specialty->name }}</div></div><div class="toolbar"><a class="button secondary" href="{{ route('patients.show', $consultation->patient) }}">Expediente</a><a class="button secondary" href="{{ route('appointments.index', ['date' => $consultation->consultation_date->toDateString()]) }}">Agenda</a></div></section>
<section class="profile-grid">
    <div style="display:grid;gap:15px;align-content:start">
        <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Evaluación clínica</h2><div class="panel-subtitle">{{ $consultation->chief_complaint ?: 'Motivo no especificado' }}</div></div></div><div class="detail-list">
            @foreach ([['Diagnóstico', $consultation->diagnosis], ['Plan de tratamiento', $consultation->treatment_plan], ['Notas de evolución', $consultation->clinical_notes]] as [$label, $value])<div class="detail-item" style="grid-template-columns:140px 1fr"><span class="detail-label">{{ $label }}</span><span class="detail-value">{{ $value ?: 'No registrado' }}</span></div>@endforeach
        </div></div>
        <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Prescripción</h2><div class="panel-subtitle">Medicamentos e indicaciones</div></div><span class="status">{{ $consultation->prescriptions->count() }} ítems</span></div>
            @forelse ($consultation->prescriptions as $prescription)<div class="detail-list" style="border-bottom:1px solid #edf1ee"><div class="person-cell">{{ $prescription->medication }}</div><div class="sub-cell">{{ $prescription->dosage }} · {{ $prescription->frequency }}{{ $prescription->duration ? ' · '.$prescription->duration : '' }}</div>@if ($prescription->instructions)<div class="detail-value" style="margin-top:7px">{{ $prescription->instructions }}</div>@endif</div>@empty<div class="empty-state">No se registraron medicamentos.</div>@endforelse
        </div>
    </div>
    <div style="display:grid;gap:15px;align-content:start">
        <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Signos vitales</h2><div class="panel-subtitle">Valores registrados durante la atención</div></div></div><div class="detail-list">
            @foreach ([['Presión arterial', $consultation->blood_pressure], ['Temperatura', $consultation->temperature ? $consultation->temperature.' °C' : null], ['Peso', $consultation->weight_kg ? $consultation->weight_kg.' kg' : null], ['Estatura', $consultation->height_cm ? $consultation->height_cm.' cm' : null], ['Seguimiento', $consultation->follow_up_date?->format('d/m/Y')]] as [$label, $value])<div class="detail-item"><span class="detail-label">{{ $label }}</span><span class="detail-value">{{ $value ?: 'No registrado' }}</span></div>@endforeach
        </div></div>
        <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Contexto del paciente</h2><div class="panel-subtitle">Información relevante para la atención</div></div></div><div class="detail-list"><div class="alert-note"><strong>Alergias:</strong> {{ $consultation->patient->allergies ?: 'No registradas' }}</div><div class="detail-item"><span class="detail-label">Antecedentes</span><span class="detail-value">{{ $consultation->patient->medical_history ?: 'No registrados' }}</span></div></div></div>
    </div>
</section>
@endsection