<x-guest-layout>
    <section class="auth-card">
    <span class="auth-kicker">HCB SALUD · PORTAL SEGURO</span>
    <h2>Iniciar sesión</h2>
    <p>Acceso para pacientes y personal autorizado de la clínica.</p>
    @if (session('status'))<div class="auth-status">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="auth-field">
            <label for="email">Correo institucional</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <div class="auth-field">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <div class="auth-options">
            <label for="remember"><input id="remember" type="checkbox" name="remember"> Mantener sesión</label>
            @if (Route::has('password.request'))<a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>@endif
        </div>
        <button class="auth-submit" type="submit">Entrar a HCB Salud</button>
    </form>
    <p class="auth-register-link">¿Eres paciente y aún no tienes cuenta? <a href="{{ route('register') }}">Solicita acceso</a></p>
    </section>
</x-guest-layout>
