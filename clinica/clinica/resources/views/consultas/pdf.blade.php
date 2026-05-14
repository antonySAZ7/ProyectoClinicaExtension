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

    <h2>Diagnostico</h2>
    <p>{{ $consulta->diagnostico }}</p>

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
