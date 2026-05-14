@php
    $paciente = $cita->paciente;
@endphp

<p>Hola {{ $paciente?->nombre_completo ?? 'paciente' }},</p>

<p>Tu solicitud de cita fue registrada correctamente.</p>

<ul>
    <li><strong>Fecha:</strong> {{ $cita->fecha?->format('d/m/Y') }}</li>
    <li><strong>Hora:</strong> {{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }} - {{ \Illuminate\Support\Str::of((string) $cita->hora_fin)->substr(0, 5) }}</li>
    <li><strong>Servicio:</strong> {{ $cita->servicio?->nombre ?? $cita->motivo }}</li>
    <li><strong>Estado:</strong> {{ ucfirst($cita->estado) }}</li>
</ul>

<p>Gracias por confiar en DENS32.</p>
