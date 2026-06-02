<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nueva cita creada</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h1 style="font-size: 20px; margin-bottom: 12px;">Nueva cita creada</h1>

    <p>Se registro una nueva cita en DENS32.</p>

    <ul>
        <li><strong>Paciente:</strong> {{ $cita->paciente?->nombre_completo ?? 'No disponible' }}</li>
        <li><strong>Correo:</strong> {{ $cita->paciente?->correo ?? 'No disponible' }}</li>
        <li><strong>Telefono:</strong> {{ $cita->paciente?->telefono ?? 'No disponible' }}</li>
        <li><strong>Fecha:</strong> {{ $cita->fecha?->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }}@if ($cita->hora_fin) - {{ \Illuminate\Support\Str::of((string) $cita->hora_fin)->substr(0, 5) }}@endif</li>
        <li><strong>Servicio:</strong> {{ $cita->servicio?->nombre ?? $cita->motivo }}</li>
        <li><strong>Estado:</strong> {{ ucfirst((string) $cita->estado) }}</li>
    </ul>

    <p>Ingresa al panel administrativo para revisar o confirmar la cita.</p>
</body>
</html>
