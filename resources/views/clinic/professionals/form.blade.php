@extends('clinic.layouts.app')

@section('title', 'Añadir profesional')

@section('content')
<section class="page-heading"><div><span class="eyebrow-light">GESTIÓN DEL EQUIPO</span><h1>Añadir profesional</h1><p>Vincula una cuenta de médico para limitar sus expedientes a su propia agenda.</p></div><a class="button secondary" href="{{ route('professionals.index') }}">Volver</a></section>
<form class="form-panel" method="POST" action="{{ route('professionals.store') }}">
    @csrf
    <h2 class="form-section-title">Perfil profesional</h2>
    <div class="form-grid">
        <div class="field"><label for="full_name">Nombre completo *</label><input id="full_name" name="full_name" value="{{ old('full_name') }}" required maxlength="255" autocomplete="name"><small class="field-error">{{ $errors->first('full_name') }}</small></div>
        <div class="field"><label for="specialty_id">Especialidad *</label><select id="specialty_id" name="specialty_id" required><option value="">Selecciona una especialidad</option>@foreach ($specialties as $specialty)<option value="{{ $specialty->id }}" @selected(old('specialty_id') == $specialty->id)>{{ $specialty->name }}</option>@endforeach</select><small class="field-error">{{ $errors->first('specialty_id') }}</small></div>
        <div class="field"><label for="license_number">Registro profesional</label><input id="license_number" name="license_number" value="{{ old('license_number') }}" maxlength="100"><small class="field-error">{{ $errors->first('license_number') }}</small></div>
        <div class="field"><label for="user_id">Cuenta de acceso de médico</label><select id="user_id" name="user_id"><option value="">Sin cuenta vinculada</option>@foreach ($doctorUsers as $doctor)<option value="{{ $doctor->id }}" @selected(old('user_id') == $doctor->id)>{{ $doctor->name }} · {{ $doctor->email }}</option>@endforeach</select><small>Las cuentas se crean con <code>php artisan clinic:user</code>.</small><small class="field-error">{{ $errors->first('user_id') }}</small></div>
        <div class="field"><label for="email">Correo de contacto</label><input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255"><small class="field-error">{{ $errors->first('email') }}</small></div>
        <div class="field"><label for="phone">Teléfono de contacto</label><input id="phone" name="phone" value="{{ old('phone') }}" maxlength="40"></div>
    </div>
    <div class="form-actions"><a class="button secondary" href="{{ route('professionals.index') }}">Cancelar</a><button class="button" type="submit">Guardar profesional</button></div>
</form>
@endsection