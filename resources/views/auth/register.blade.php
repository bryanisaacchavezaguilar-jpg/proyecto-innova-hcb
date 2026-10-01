<x-guest-layout>
    <section class="auth-card">
        <span class="auth-kicker">HCB SALUD · PORTAL DE PACIENTES</span>
        <h2>Solicitar acceso</h2>
        <p>Completa tus datos. El equipo verificará tu identidad antes de habilitar el expediente.</p>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="auth-field"><label for="name">Nombre completo *</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">@error('name')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="email">Correo electrónico *</label><input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">@error('email')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="document_type">Tipo de documento *</label><select id="document_type" name="document_type" required>@foreach (['DNI', 'CC', 'CE', 'Pasaporte', 'Otro'] as $type)<option value="{{ $type }}" @selected(old('document_type', 'DNI') === $type)>{{ $type }}</option>@endforeach</select>@error('document_type')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="document_number">Número de documento *</label><input id="document_number" name="document_number" value="{{ old('document_number') }}" required maxlength="50" autocomplete="off">@error('document_number')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="date_of_birth">Fecha de nacimiento *</label><input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ today()->toDateString() }}" required autocomplete="bday">@error('date_of_birth')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="phone">Teléfono</label><input id="phone" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">@error('phone')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password">Contraseña (mínimo 12 caracteres) *</label><input id="password" type="password" name="password" required minlength="12" autocomplete="new-password">@error('password')<span class="auth-error">{{ $message }}</span>@enderror</div>
            <div class="auth-field"><label for="password_confirmation">Confirmar contraseña *</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></div>
            <button class="auth-submit" type="submit">Enviar solicitud de acceso</button>
        </form>
        <p class="auth-register-link">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        <p class="portal-privacy">No compartiremos el acceso hasta que el personal verifique tus datos con el expediente clínico correspondiente.</p>
    </section>
</x-guest-layout>
