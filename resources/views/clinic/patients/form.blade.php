@extends('clinic.layouts.app')

@section('title', $patient->exists ? 'Editar paciente' : 'Nuevo paciente')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">EXPEDIENTE CLÍNICO</span><h1>{{ $patient->exists ? 'Actualizar paciente' : 'Registrar paciente' }}</h1><p>Los campos con datos personales deben coincidir con la documentación oficial.</p></div><a class="button secondary" href="{{ $patient->exists ? route('patients.show', $patient) : route('patients.index') }}">Volver</a></section>
<form class="form-panel" method="POST" action="{{ $patient->exists ? route('patients.update', $patient) : route('patients.store') }}">
    @csrf @if ($patient->exists) @method('PUT') @endif
    <h2 class="form-section-title">Identificación</h2>
    <div class="form-grid three">
        <div class="field"><label for="first_name">Nombres *</label><input id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required maxlength="100" autocomplete="given-name"><small class="field-error">{{ $errors->first('first_name') }}</small></div>
        <div class="field"><label for="last_name">Apellidos *</label><input id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required maxlength="100" autocomplete="family-name"><small class="field-error">{{ $errors->first('last_name') }}</small></div>
        <div class="field"><label for="document_type">Tipo de documento *</label><select id="document_type" name="document_type" required>@foreach (['DNI', 'CC', 'CE', 'Pasaporte', 'Otro'] as $type)<option value="{{ $type }}" @selected(old('document_type', $patient->document_type ?: 'DNI') === $type)>{{ $type }}</option>@endforeach</select></div>
        <div class="field"><label for="document_number">Número de documento *</label><input id="document_number" name="document_number" value="{{ old('document_number', $patient->document_number) }}" required maxlength="50"><small class="field-error">{{ $errors->first('document_number') }}</small></div>
        <div class="field"><label for="date_of_birth">Fecha de nacimiento</label><input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}"></div>
        <div class="field"><label for="sex">Sexo</label><select id="sex" name="sex"><option value="">No especificado</option>@foreach (['Femenino', 'Masculino', 'Intersexual', 'Prefiere no indicar'] as $option)<option value="{{ $option }}" @selected(old('sex', $patient->sex) === $option)>{{ $option }}</option>@endforeach</select></div>
    </div>
    <h2 class="form-section-title">Contacto</h2>
    <div class="form-grid">
        <div class="field"><label for="phone">Teléfono</label><input id="phone" name="phone" value="{{ old('phone', $patient->phone) }}" maxlength="40" autocomplete="tel"></div>
        <div class="field"><label for="email">Correo electrónico</label><input id="email" type="email" name="email" value="{{ old('email', $patient->email) }}" maxlength="255" autocomplete="email"><small class="field-error">{{ $errors->first('email') }}</small></div>
        <div class="field span-2"><label for="address">Dirección</label><input id="address" name="address" value="{{ old('address', $patient->address) }}" maxlength="2000" autocomplete="street-address"></div>
        <div class="field span-2"><label for="emergency_contact">Contacto de emergencia</label><input id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $patient->emergency_contact) }}" maxlength="255" placeholder="Nombre y teléfono"></div>
    </div>
    @if (auth()->user()->role !== 'receptionist')
        <h2 class="form-section-title">Antecedentes médicos</h2>
        <div class="form-grid">
            <div class="field"><label for="allergies">Alergias</label><textarea id="allergies" name="allergies" maxlength="5000" placeholder="Alergias conocidas o indicar 'Ninguna conocida'">{{ old('allergies', $patient->allergies) }}</textarea></div>
            <div class="field"><label for="medical_history">Antecedentes relevantes</label><textarea id="medical_history" name="medical_history" maxlength="10000" placeholder="Condiciones previas, cirugías, tratamientos...">{{ old('medical_history', $patient->medical_history) }}</textarea></div>
        </div>
    @endif
    @if ($patient->exists)<div class="field" style="margin-top:14px"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $patient->is_active))> Expediente activo</label></div>@endif
    <div class="form-actions"><a class="button secondary" href="{{ $patient->exists ? route('patients.show', $patient) : route('patients.index') }}">Cancelar</a><button class="button" type="submit">{{ $patient->exists ? 'Guardar cambios' : 'Crear expediente' }}</button></div>
</form>
@endsection