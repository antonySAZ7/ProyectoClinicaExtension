@php
    $paciente = $recordatorio->paciente;
    $servicio = $recordatorio->cita?->servicio?->nombre ?? 'seguimiento dental';
    $titulo = $recordatorio->displayTitle();
@endphp

<p>Hola {{ $paciente?->nombre_completo ?? 'paciente' }},</p>

<h2>{{ $titulo }}</h2>

@if ($recordatorio->mensaje)
    <p>{{ $recordatorio->mensaje }}</p>
@else
    <p>Te recordamos que ya corresponde agendar tu {{ $servicio }}.</p>
    <p>Fecha sugerida: <strong>{{ $recordatorio->fecha_objetivo?->format('d/m/Y') }}</strong>.</p>
@endif

<p>Estamos listos para atenderte en DENS32.</p>
