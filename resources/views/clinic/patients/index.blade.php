@extends('clinic.layouts.app')

@section('title', 'Pacientes')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">DIRECTORIO CLÍNICO</span><h1>Pacientes</h1><p>{{ $patients->total() }} expedientes encontrados. Busca por nombre o documento.</p></div>@if (in_array(auth()->user()->role, ['admin', 'receptionist', 'nurse'], true))<a class="button" href="{{ route('patients.create') }}">+ Registrar paciente</a>@endif</section>
<div class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Expedientes</h2><div class="panel-subtitle">La información médica solo está disponible para personal autorizado.</div></div><form class="search-form" method="GET" action="{{ route('patients.index') }}"><input class="control" type="search" name="q" value="{{ $search }}" placeholder="Nombre o documento" aria-label="Buscar pacientes"><button class="button secondary small" type="submit">Buscar</button>@if ($search)<a class="button secondary small" href="{{ route('patients.index') }}">Limpiar</a>@endif</form></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>PACIENTE</th><th>DOCUMENTO</th><th>CONTACTO</th><th>ESTADO</th><th></th></tr></thead><tbody>
    @forelse ($patients as $patient)
        <tr><td><a class="person-cell" href="{{ route('patients.show', $patient) }}">{{ $patient->full_name }}</a><span class="sub-cell">{{ $patient->date_of_birth?->age ? $patient->date_of_birth->age.' años' : 'Edad no registrada' }}{{ $patient->sex ? ' · '.$patient->sex : '' }}</span></td><td>{{ $patient->document_type }}<span class="sub-cell">{{ $patient->document_number }}</span></td><td>{{ $patient->phone ?: 'Sin teléfono' }}<span class="sub-cell">{{ $patient->email ?: 'Sin correo' }}</span></td><td><span class="status {{ $patient->is_active ? 'status-confirmed' : 'status-cancelled' }}">{{ $patient->is_active ? 'Activo' : 'Inactivo' }}</span></td><td><a class="text-link" href="{{ route('patients.show', $patient) }}">Abrir expediente →</a></td></tr>
    @empty
        <tr><td colspan="5"><div class="empty-state">{{ $search ? 'No hay coincidencias para esa búsqueda.' : 'Aún no hay pacientes registrados.' }}</div></td></tr>
    @endforelse
    </tbody></table></div>
    @if ($patients->hasPages())<div class="pagination">{{ $patients->links() }}</div>@endif
</div>
@endsection