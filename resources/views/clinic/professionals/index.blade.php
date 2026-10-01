@extends('clinic.layouts.app')

@section('title', 'Equipo clínico')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">PERSONAL AUTORIZADO</span><h1>Equipo clínico</h1><p>Directorio de profesionales habilitados para la atención.</p></div>@if (auth()->user()->role === 'admin')<a class="button" href="{{ route('professionals.create') }}">+ Añadir profesional</a>@endif</section>
<div class="panel"><div class="panel-head"><div><h2 class="panel-title">Profesionales activos</h2><div class="panel-subtitle">{{ $professionals->count() }} perfiles disponibles para agendar</div></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>PROFESIONAL</th><th>ESPECIALIDAD</th><th>REGISTRO</th><th>CONTACTO</th><th></th></tr></thead><tbody>
    @forelse ($professionals as $professional)<tr><td class="person-cell">{{ $professional->full_name }}<span class="sub-cell">{{ $professional->user?->email ?: 'Sin cuenta vinculada' }}</span></td><td>{{ $professional->specialty->name }}</td><td>{{ $professional->license_number ?: 'No registrado' }}</td><td>{{ $professional->phone ?: 'Sin teléfono' }}<span class="sub-cell">{{ $professional->email ?: 'Sin correo' }}</span></td><td><div style="display:flex;align-items:center;gap:8px"><span class="status {{ $professional->is_active ? 'status-confirmed' : 'status-cancelled' }}">{{ $professional->is_active ? 'Activo' : 'Inactivo' }}</span>@if (auth()->user()->role === 'admin')<form method="POST" action="{{ route('professionals.status', $professional) }}">@csrf @method('PATCH')<button class="button secondary small" type="submit">{{ $professional->is_active ? 'Desactivar' : 'Activar' }}</button></form>@endif</div></td></tr>@empty<tr><td colspan="5"><div class="empty-state">No hay profesionales. Añade perfiles para habilitar la agenda.</div></td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection