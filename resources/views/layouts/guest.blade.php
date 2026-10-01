<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-body">
        <div class="auth-shell">
            <aside class="auth-aside">
                <a class="auth-brand" href="/"><span class="auth-mark">H</span><span><strong>HCB</strong><small>Centro de salud</small></span></a>
                <div class="auth-aside-copy"><span class="auth-kicker">GESTIÓN CLÍNICA</span><h1>Atención coordinada.<br>Información cuidada.</h1><p>Plataforma interna para organizar la agenda y acompañar cada expediente con continuidad.</p></div>
                <div class="auth-aside-foot"><span class="privacy-dot"></span>Acceso exclusivo para personal autorizado</div>
            </aside>
            <main class="auth-main">{{ $slot }}</main>
        </div>
    </body>
</html>
