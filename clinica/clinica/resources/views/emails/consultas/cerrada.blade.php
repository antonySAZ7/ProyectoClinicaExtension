@php
    $paciente = $consulta->paciente;
@endphp

<p>Hola {{ $paciente?->nombre_completo ?? 'paciente' }},</p>

<p>Te compartimos el resumen de tu consulta en DENS32.</p>

<ul>
    <li><strong>Fecha:</strong> {{ $consulta->fecha?->format('d/m/Y') }}</li>
    <li><strong>Motivo:</strong> {{ $consulta->motivo }}</li>
    <li><strong>Diagnóstico:</strong> {{ $consulta->diagnostico }}</li>
</ul>

@if ($consulta->observaciones->isNotEmpty())
    <p><strong>Observaciones:</strong></p>
    <ul>
        @foreach ($consulta->observaciones->sortBy('created_at') as $observacion)
            <li>{{ $observacion->descripcion }}</li>
        @endforeach
    </ul>
@endif

<p>
    <strong>Saldo actual:</strong>
    Q{{ number_format((float) ($paciente?->saldo_pendiente ?? 0), 2) }}
</p>

<p>
    Puedes ingresar a tu portal para revisar tu historial clinico:
    <a href="{{ route('portal') }}">Ver portal</a>
</p>

<p>Gracias por confiar en nosotros.</p>
