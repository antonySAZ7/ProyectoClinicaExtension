<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Consulta {{ $consulta->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
        h1, h2 { margin: 0 0 8px; }
        h1 { font-size: 24px; }
        h2 { font-size: 16px; margin-top: 22px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #666; }
        .grid { display: table; width: 100%; margin-top: 12px; }
        .row { display: table-row; }
        .cell { display: table-cell; width: 50%; padding: 4px 12px 4px 0; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; background: #eef2ff; }
    </style>
</head>
<body>
    <h1>Consulta clinica</h1>
    <p class="muted">DENS32 Clinica Dental</p>

    <div class="grid">
        <div class="row">
            <div class="cell"><strong>Paciente:</strong> {{ $consulta->paciente?->nombre_completo }}</div>
            <div class="cell"><strong>Fecha:</strong> {{ $consulta->fecha?->format('d/m/Y') }}</div>
        </div>
        <div class="row">
            <div class="cell"><strong>DPI:</strong> {{ $consulta->paciente?->dpi }}</div>
            <div class="cell"><strong>Registrado por:</strong> {{ $consulta->user?->name }}</div>
        </div>
    </div>

    <h2>Motivo</h2>
    <p>{{ $consulta->motivo }}</p>

    <h2>Diagnóstico</h2>
    <p>{{ $consulta->diagnostico }}</p>

    @php
        $tieneSignos = $consulta->peso || $consulta->altura || $consulta->presion_arterial
            || $consulta->frecuencia_cardiaca || $consulta->frecuencia_respiratoria || $consulta->signos_otros;
    @endphp
    @if ($tieneSignos)
        <h2>Signos vitales</h2>
        <div class="grid">
            <div class="row">
                <div class="cell"><strong>Peso:</strong> {{ $consulta->peso ? $consulta->peso.' kg' : '-' }}</div>
                <div class="cell"><strong>Altura:</strong> {{ $consulta->altura ? $consulta->altura.' m' : '-' }}</div>
            </div>
            <div class="row">
                <div class="cell"><strong>Presion arterial:</strong> {{ $consulta->presion_arterial ?: '-' }}</div>
                <div class="cell"><strong>Frec. cardiaca:</strong> {{ $consulta->frecuencia_cardiaca ? $consulta->frecuencia_cardiaca.' lpm' : '-' }}</div>
            </div>
            <div class="row">
                <div class="cell"><strong>Frec. respiratoria:</strong> {{ $consulta->frecuencia_respiratoria ? $consulta->frecuencia_respiratoria.' rpm' : '-' }}</div>
                <div class="cell"><strong>Otros:</strong> {{ $consulta->signos_otros ?: '-' }}</div>
            </div>
        </div>
    @endif

    @php $ant = $consulta->paciente?->antecedenteClinico; @endphp
    @if ($ant)
        @php
            $medicosSi = collect(\App\Models\AntecedenteClinico::CAMPOS_MEDICOS)->filter(fn ($e, $c) => $ant->$c)->values();
            $odontoSi = collect(\App\Models\AntecedenteClinico::CAMPOS_ODONTOLOGICOS)->filter(fn ($e, $c) => $ant->$c)->values();
        @endphp
        <h2>Antecedentes medicos</h2>
        <p>{{ $medicosSi->isNotEmpty() ? $medicosSi->implode(', ') : 'Ninguno marcado.' }}</p>

        <h2>Antecedentes odontologicos</h2>
        <p>{{ $odontoSi->isNotEmpty() ? $odontoSi->implode(', ') : 'Ninguno marcado.' }}</p>

        @if ($ant->toma_medicamento && $ant->cual_medicamento)
            <p><strong>Medicamento que toma:</strong> {{ $ant->cual_medicamento }}</p>
        @endif
        @if ($ant->alergico_medicamento && $ant->cuales_medicamentos)
            <p><strong>Alergias a medicamentos:</strong> {{ $ant->cuales_medicamentos }}</p>
        @endif
        @if ($ant->descripcion_enfermedades)
            <p><strong>Descripcion de enfermedades:</strong> {{ $ant->descripcion_enfermedades }}</p>
        @endif
    @endif

    <h2>Observaciones</h2>
    @forelse ($consulta->observaciones as $observacion)
        <p>{{ $observacion->descripcion }}</p>
    @empty
        <p class="muted">Sin observaciones registradas.</p>
    @endforelse

    <h2>Odontograma</h2>
    <table>
        <thead>
            <tr>
                <th>Pieza</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($piezas as $pieza)
                <tr>
                    <td>{{ $pieza->numero }}</td>
                    <td>{{ $pieza->nombre }}</td>
                    <td><span class="badge">{{ ucfirst($pieza->estado_odontograma) }}</span></td>
                    <td>{{ $pieza->observaciones_odontograma ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
