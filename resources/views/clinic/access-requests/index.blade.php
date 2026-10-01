@extends('clinic.layouts.app')

@section('title', 'Solicitudes de acceso')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">IDENTIDAD Y PERMISOS</span><h1>Solicitudes de acceso</h1><p>Verifica los datos con el paciente antes de vincular una cuenta a su expediente.</p></div></section>
<div class="panel"><div class="panel-head"><div><h2 class="panel-title">Pendientes de revisión</h2><div class="panel-subtitle">{{ $requests->count() }} solicitudes sin vincular</div></div></div>
    @forelse ($requests as $item)
        @php($accessRequest = $item['request'])
        <div class="access-request-row">
            <div><strong>{{ $accessRequest->user->name }}</strong><small>{{ $accessRequest->user->email }}{{ $accessRequest->phone ? ' · '.$accessRequest->phone : '' }}</small></div>
            <div><strong>{{ $accessRequest->document_type }} · {{ $accessRequest->document_number }}</strong><small>Fecha de nacimiento: {{ $accessRequest->date_of_birth?->format('d/m/Y') ?: 'No indicada' }}</small><small>Solicitud: {{ $accessRequest->created_at->format('d/m/Y H:i') }}</small></div>
            @if ($item['candidates']->isNotEmpty())<form method="POST" action="{{ route('access-requests.approve', $accessRequest) }}">@csrf<select class="control" name="patient_id" required aria-label="Selecciona el expediente que coincide"><option value="">Expediente que coincide</option>@foreach ($item['candidates'] as $candidate)<option value="{{ $candidate->id }}">{{ $candidate->full_name }} · {{ $candidate->document_type }} {{ $candidate->document_number }}</option>@endforeach</select><button class="button small" type="submit">Verifiqué y vincular</button></form>
            @else<div><span class="status status-scheduled">Sin coincidencia exacta</span><small>Revisa el documento y la fecha con el paciente; crea o corrige primero el expediente.</small></div>@endif
        </div>
    @empty
        <div class="empty-state">No hay solicitudes pendientes de revisión.</div>
    @endforelse
</div>
@endsection