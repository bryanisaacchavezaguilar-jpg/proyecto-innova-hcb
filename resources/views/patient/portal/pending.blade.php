<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud en revisión | HCB Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-shell">
    <header class="portal-header"><a class="portal-brand" href="{{ route('patient.portal') }}"><span class="portal-brand-mark">H</span>HCB Salud</a><div class="portal-user">{{ auth()->user()->name }}<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form></div></header>
    <main class="portal-content">
        <span class="eyebrow-light">PORTAL DE PACIENTES</span>
        <h1 class="portal-title">Tu solicitud está en revisión</h1>
        <p class="portal-lead">Aún no tienes acceso al expediente clínico.</p>
        <section class="portal-panel" style="max-width:680px;margin-top:22px"><div class="portal-panel-body">
            <div class="portal-request-status"><strong>Verificación pendiente</strong>El personal de HCB debe revisar tus datos y vincularlos con un expediente existente. Hasta entonces no se mostrará información clínica ni estarán disponibles las citas.</div>
            @if ($accessRequest)<div class="portal-details" style="margin-top:20px"><div class="portal-detail"><span>Documento enviado</span><span>{{ $accessRequest->document_type }} · termina en {{ substr($accessRequest->document_number, -4) }}</span></div><div class="portal-detail"><span>Solicitud enviada</span><span>{{ $accessRequest->created_at->format('d/m/Y') }}</span></div><div class="portal-detail"><span>Estado</span><span>Pendiente de revisión</span></div></div>@endif
            <p class="portal-privacy">Si tus datos requieren corrección o no encuentras actualización, contacta directamente a la clínica. No envíes contraseñas ni fotografías de documentos por correo.</p>
        </div></section>
    </main>
</body>
</html>