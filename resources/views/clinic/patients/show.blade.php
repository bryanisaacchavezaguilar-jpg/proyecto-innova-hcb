@extends('clinic.layouts.app')

@section('title', 'Expediente del paciente')

@section('content')
<section class="profile-head"><div><span class="eyebrow-light">EXPEDIENTE · {{ $patient->document_type }} {{ $patient->document_number }}</span><div class="profile-name">{{ $patient->full_name }}</div><div class="profile-doc">{{ $patient->date_of_birth?->age ? $patient->date_of_birth->age.' años' : 'Edad no registrada' }}{{ $patient->sex ? ' · '.$patient->sex : '' }} · Registrado {{ $patient->created_at->format('d/m/Y') }}</div></div><div class="toolbar"><a class="button secondary" href="{{ route('patients.index') }}">Directorio</a>@if (in_array(auth()->user()->role, ['admin', 'receptionist', 'nurse'], true))<a class="button secondary" href="{{ route('patients.edit', $patient) }}">Editar datos</a><a class="button" href="{{ route('appointments.create', ['patient' => $patient->id]) }}">+ Agendar cita</a>@endif</div></section>
@if (!$patient->is_active)<div class="flash flash-error">Este expediente está inactivo. Puedes consultar la información histórica, pero no agendar nuevas citas.</div>@endif
<section class="profile-grid">
    <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Datos del paciente</h2><div class="panel-subtitle">Contacto y antecedentes registrados</div></div><span class="status {{ $patient->is_active ? 'status-confirmed' : 'status-cancelled' }}">{{ $patient->is_active ? 'Activo' : 'Inactivo' }}</span></div><div class="detail-list">
        @foreach ([['Teléfono', $patient->phone], ['Correo', $patient->email], ['Dirección', $patient->address], ['Contacto de emergencia', $patient->emergency_contact]] as [$label, $value])<div class="detail-item"><span class="detail-label">{{ $label }}</span><span class="detail-value">{{ $value ?: 'No registrado' }}</span></div>@endforeach
        @if (auth()->user()->role !== 'receptionist')
            <div class="alert-note"><strong>Alergias:</strong> {{ $patient->allergies ?: 'No registradas' }}</div>
            <div class="detail-item"><span class="detail-label">Antecedentes</span><span class="detail-value">{{ $patient->medical_history ?: 'No registrados' }}</span></div>
        @else
            <div class="detail-item"><span class="detail-label">Datos clínicos</span><span class="detail-value">Disponibles solo para personal clínico autorizado.</span></div>
        @endif
    </div></div>
    <div style="display:grid;gap:15px;align-content:start">
        @if (auth()->user()->role !== 'receptionist')<div class="panel"><div class="panel-head"><div><h2 class="panel-title">Consultas clínicas</h2><div class="panel-subtitle">Notas y prescripciones del expediente</div></div><span class="status">{{ $patient->consultations->count() }} recientes</span></div>
            @forelse ($patient->consultations as $consultation)<a class="recent-row" style="margin:0 18px" href="{{ route('consultations.show', $consultation) }}"><span class="patient-avatar">{{ $consultation->consultation_date->format('d') }}</span><span><span class="recent-name">{{ $consultation->diagnosis ?: $consultation->chief_complaint ?: 'Consulta clínica' }}</span><span class="recent-meta">{{ $consultation->consultation_date->format('d/m/Y H:i') }} · {{ $consultation->professional->full_name }}</span></span><span class="recent-id">→</span></a>@empty<div class="empty-state">Aún no hay consultas clínicas.</div>@endforelse
        </div>@endif
        <div class="panel"><div class="panel-head"><div><h2 class="panel-title">Historial de citas</h2><div class="panel-subtitle">Últimos turnos registrados</div></div></div>
            @forelse ($patient->appointments as $appointment)<div class="recent-row" style="margin:0 18px"><span class="patient-avatar">{{ $appointment->starts_at->format('d') }}</span><span><span class="recent-name">{{ $appointment->starts_at->format('d/m/Y H:i') }} · {{ $appointment->professional->full_name }}</span><span class="recent-meta">{{ $appointment->reason ?: 'Consulta general' }}</span></span><span class="status status-{{ $appointment->status }}">{{ ['requested' => 'Por confirmar', 'scheduled' => 'Agendada', 'confirmed' => 'Confirmada', 'in_progress' => 'En consulta', 'completed' => 'Completada', 'cancelled' => 'Cancelada', 'no_show' => 'No asistió'][$appointment->status] }}</span></div>@empty<div class="empty-state">No hay citas registradas.</div>@endforelse
        </div>
    </div>
</section>
@endsection