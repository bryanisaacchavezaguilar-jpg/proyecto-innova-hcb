<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi portal | HCB Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-shell">
    <header class="portal-header"><a class="portal-brand" href="{{ route('patient.portal') }}"><span class="portal-brand-mark">H</span>HCB Salud</a><div class="portal-user">{{ $user->name }}<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form></div></header>
    <main class="portal-content">
        @if (session('success'))<div class="flash flash-success" role="status">{{ session('success') }}</div>@endif
        <span class="eyebrow-light">TU ESPACIO PERSONAL</span>
        <h1 class="portal-title">Hola, {{ Str::before($user->name, ' ') }}.</h1>
        <p class="portal-lead">Aquí puedes consultar tus citas y solicitar una nueva atención.</p>
        <div style="margin-top:18px"><a class="button" href="{{ route('patient.appointments.create') }}">+ Solicitar cita</a></div>
        <section class="portal-columns">
            <div class="portal-panel"><div class="portal-panel-head"><div><h2>Próximas citas</h2><span class="panel-subtitle">Las solicitudes nuevas requieren confirmación de la clínica.</span></div></div>
                @forelse ($appointments as $appointment)<div class="portal-panel-body" style="padding-bottom:0"><div class="portal-appointment"><div class="portal-date">{{ $appointment->starts_at->locale('es')->translatedFormat('j M Y') }}<small>{{ $appointment->starts_at->format('H:i') }}</small></div><div class="portal-provider"><strong>{{ $appointment->professional->full_name }}</strong><small>{{ $appointment->professional->specialty->name }}{{ $appointment->reason ? ' · '.$appointment->reason : '' }}</small></div><div><span class="portal-status {{ $appointment->status }}">{{ ['requested' => 'Por confirmar', 'scheduled' => 'Agendada', 'confirmed' => 'Confirmada', 'in_progress' => 'En atención'][$appointment->status] }}</span>@if ($appointment->status === 'requested')<form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" style="margin-top:7px">@csrf @method('DELETE')<button class="text-link" type="submit">Cancelar solicitud</button></form>@endif</div></div></div>@empty<div class="portal-empty">No tienes citas próximas. Puedes solicitar una desde aquí.</div>@endforelse
            </div>
            <aside class="portal-panel"><div class="portal-panel-head"><h2>Mis datos</h2></div><div class="portal-panel-body portal-details"><div class="portal-detail"><span>Nombre</span><span>{{ $user->patient->full_name }}</span></div><div class="portal-detail"><span>Documento</span><span>{{ $user->patient->document_type }} · ••••{{ substr($user->patient->document_number, -4) }}</span></div><div class="portal-detail"><span>Correo</span><span>{{ $user->email }}</span></div><div class="portal-detail"><span>Teléfono</span><span>{{ $user->patient->phone ?: 'No registrado' }}</span></div><p class="portal-privacy">Los datos clínicos y notas médicas no se muestran en este portal. Para corregir tus datos, comunícate con HCB.</p></div></aside>
        </section>
    </main>
</body>
</html>