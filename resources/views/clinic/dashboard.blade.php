@extends('clinic.layouts.app')

@section('title', 'Resumen')

@section('content')
<section class="page-heading">
    <div><span class="eyebrow-light">{{ now()->locale('es')->translatedFormat('l, j \d\e F Y') }}</span><h1>Buenos días, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Este es el pulso de la clínica para hoy.</p></div>
    @if (in_array(auth()->user()->role, ['admin', 'receptionist', 'nurse'], true))<a class="button" href="{{ route('appointments.create') }}">+ Agendar cita</a>@endif
</section>

<section class="metrics" aria-label="Indicadores de hoy">
    <div class="metric"><div class="metric-label">Citas de hoy</div><div class="metric-value">{{ $todayCount }}</div><div class="metric-foot">En toda la clínica</div></div>
    <div class="metric"><div class="metric-label">Por atender</div><div class="metric-value">{{ $waitingCount }}</div><div class="metric-foot">Solicitadas, agendadas o confirmadas</div></div>
    <div class="metric"><div class="metric-label">Pacientes activos</div><div class="metric-value">{{ number_format($patientCount) }}</div><div class="metric-foot">Expedientes registrados</div></div>
    <div class="metric"><div class="metric-label">Equipo clínico</div><div class="metric-value">{{ $professionalCount }}</div><div class="metric-foot">Profesionales activos</div></div>
</section>

<section class="content-grid">
    <div class="panel">
        <div class="panel-head"><div><h2 class="panel-title">Agenda de hoy</h2><div class="panel-subtitle">{{ now()->locale('es')->translatedFormat('j \d\e F') }} · ordenada por hora</div></div><a class="text-link" href="{{ route('appointments.index', ['date' => today()->toDateString()]) }}">Ver agenda completa →</a></div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>HORA</th><th>PACIENTE</th><th>PROFESIONAL</th><th>ESTADO</th></tr></thead><tbody>
        @forelse ($appointments as $appointment)
            <tr><td class="time-cell">{{ $appointment->starts_at->format('H:i') }}</td><td><a class="person-cell" href="{{ route('patients.show', $appointment->patient) }}">{{ $appointment->patient->full_name }}</a><span class="sub-cell">{{ $appointment->reason ?: 'Consulta general' }}</span></td><td>{{ $appointment->professional->full_name }}<span class="sub-cell">{{ $appointment->professional->specialty->name }}</span></td><td><span class="status status-{{ $appointment->status }}">{{ ['requested' => 'Por confirmar', 'scheduled' => 'Agendada', 'confirmed' => 'Confirmada', 'in_progress' => 'En consulta', 'completed' => 'Completada', 'cancelled' => 'Cancelada', 'no_show' => 'No asistió'][$appointment->status] }}</span></td></tr>
        @empty
            <tr><td colspan="4"><div class="empty-state">No hay citas para hoy. La agenda está despejada.</div></td></tr>
        @endforelse
        </tbody></table></div>
    </div>
    <div class="panel">
        <div class="panel-head"><div><h2 class="panel-title">Pacientes recientes</h2><div class="panel-subtitle">Últimos expedientes añadidos</div></div><a class="text-link" href="{{ route('patients.index') }}">Directorio →</a></div>
        <div class="recent-list">
        @forelse ($recentPatients as $patient)
            <a class="recent-row" href="{{ route('patients.show', $patient) }}"><span class="patient-avatar">{{ mb_substr($patient->first_name, 0, 1) }}{{ mb_substr($patient->last_name, 0, 1) }}</span><span><span class="recent-name">{{ $patient->full_name }}</span><span class="recent-meta">{{ $patient->document_type }} · {{ $patient->document_number }}</span></span><span class="recent-id">→</span></a>
        @empty
            <div class="empty-state">Aún no hay pacientes. Registra el primer expediente.</div>
        @endforelse
        </div>
        @if (in_array(auth()->user()->role, ['admin', 'receptionist', 'nurse'], true))<div style="padding:0 18px 16px"><a class="button secondary small" href="{{ route('patients.create') }}">+ Nuevo paciente</a></div>@endif
    </div>
</section>
@endsection