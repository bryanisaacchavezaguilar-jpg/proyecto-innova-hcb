<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitar cita | HCB Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-shell">
    <header class="portal-header"><a class="portal-brand" href="{{ route('patient.portal') }}"><span class="portal-brand-mark">H</span>HCB Salud</a><div class="portal-user">{{ auth()->user()->name }}<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form></div></header>
    <main class="portal-content">
        <span class="eyebrow-light">AGENDA HCB</span><h1 class="portal-title">Solicitar una cita</h1><p class="portal-lead">Elige un profesional y un horario. La clínica confirmará la disponibilidad.</p>
        @if ($errors->any())<div class="flash flash-error" style="margin-top:16px">{{ $errors->first() }}</div>@endif
        <form class="portal-form" method="POST" action="{{ route('patient.appointments.store') }}">
            @csrf
            <div class="portal-form-grid">
                <div class="field span-2"><label for="professional_id">Profesional *</label><select id="professional_id" name="professional_id" required><option value="">Selecciona un profesional</option>@foreach ($professionals as $professional)<option value="{{ $professional->id }}" @selected(old('professional_id') == $professional->id)>{{ $professional->full_name }} · {{ $professional->specialty->name }}</option>@endforeach</select>@error('professional_id')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field span-2"><label for="starts_at">Fecha y hora preferidas *</label><input id="starts_at" type="datetime-local" name="starts_at" min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" value="{{ old('starts_at') }}" required>@error('starts_at')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field span-2"><label for="reason">Motivo breve</label><input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" placeholder="Ej. Consulta general o seguimiento"></div>
            </div>
            <p class="portal-privacy">La duración inicial es de 30 minutos. No incluyas información médica sensible en este formulario; podrás hablar de ella con el personal clínico.</p>
            <div class="portal-form-actions"><a class="button secondary" href="{{ route('patient.portal') }}">Volver</a><button class="button" type="submit" @disabled($professionals->isEmpty())>Enviar solicitud</button></div>
        </form>
    </main>
</body>
</html>