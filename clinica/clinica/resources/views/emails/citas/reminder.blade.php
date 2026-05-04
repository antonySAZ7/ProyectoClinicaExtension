<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recordatorio de cita</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h1 style="font-size: 20px; margin-bottom: 12px;">Recordatorio de cita</h1>

    <p>Hola {{ $cita->paciente?->nombre_completo ?? 'paciente' }},</p>

    <p>Te recordamos que tienes una cita programada en la clinica.</p>

    <ul>
        <li><strong>Paciente:</strong> {{ $cita->paciente?->nombre_completo ?? 'No disponible' }}</li>
        <li><strong>Fecha:</strong> {{ $cita->fecha?->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }}</li>
    </ul>

    <p>Si necesitas cambiar o cancelar tu cita, ingresa al portal del paciente.</p>
</body>
</html>
