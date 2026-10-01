<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HCB Salud') | {{ config('app.name', 'HCB Salud') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="clinic-body">
    <div class="clinic-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">H</span>
                <span><strong>HCB</strong><small>Centro de salud</small></span>
            </a>
            <div class="nav-label">OPERACIÓN</div>
            <nav class="side-nav" aria-label="Navegación principal">
                <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-symbol">01</span>Resumen</a>
                <a class="nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}"><span class="nav-symbol">02</span>Pacientes</a>
                <a class="nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}"><span class="nav-symbol">03</span>Agenda</a>
                <a class="nav-item {{ request()->routeIs('professionals.*') ? 'active' : '' }}" href="{{ route('professionals.index') }}"><span class="nav-symbol">04</span>Equipo clínico</a>
                @if (auth()->user()->role === 'admin')<a class="nav-item {{ request()->routeIs('access-requests.*') ? 'active' : '' }}" href="{{ route('access-requests.index') }}"><span class="nav-symbol">05</span>Accesos pendientes</a>@endif
            </nav>
            <div class="sidebar-bottom">
                <div class="privacy-note"><span class="privacy-dot"></span><div><strong>Entorno privado</strong><small>Acceso para personal autorizado</small></div></div>
                <a class="account-link" href="{{ route('profile.edit') }}">
                    <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="account-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ ['admin' => 'Administración', 'doctor' => 'Profesional médico', 'nurse' => 'Enfermería', 'receptionist' => 'Recepción'][auth()->user()->role] ?? 'Personal' }}</small></span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="signout" type="submit">Cerrar sesión</button></form>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div><span class="eyebrow">HCB SALUD · GESTIÓN CLÍNICA</span><div class="topbar-title">@yield('title', 'Resumen')</div></div>
                <div class="topbar-date"><span class="live-dot"></span>{{ now()->locale('es')->translatedFormat('l, j \d\e F') }}</div>
            </header>
            <div class="page-content">
                @if (session('success'))<div class="flash flash-success" role="status">{{ session('success') }}</div>@endif
                @if ($errors->any())<div class="flash flash-error" role="alert">Revisa los campos marcados. {{ $errors->first() }}</div>@endif
                @yield('content')
                <footer class="page-footer"><span>HCB Salud</span><span>Información clínica confidencial</span></footer>
            </div>
        </main>
    </div>
</body>
</html>