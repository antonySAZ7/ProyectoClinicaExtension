<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de cuenta - {{ $paciente->nombre_completo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
        h1, h2 { margin: 0 0 8px; }
        h1 { font-size: 22px; }
        h2 { font-size: 15px; margin-top: 22px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #666; }
        .grid { display: table; width: 100%; margin-top: 12px; }
        .row { display: table-row; }
        .cell { display: table-cell; width: 50%; padding: 4px 12px 4px 0; }
        .right { text-align: right; }
        .num { text-align: right; white-space: nowrap; }
        .total-row td { background: #f9fafb; font-weight: bold; }
        .resumen { margin-top: 16px; width: 60%; }
        .resumen td { padding: 8px; }
        .saldo td { background: #fef2f2; font-size: 14px; font-weight: bold; color: #9f1239; }
        .ref { margin-top: 12px; padding: 8px 10px; background: #eef2ff; border: 1px solid #c7d2fe; }
        .firmas { margin-top: 48px; width: 100%; }
        .firma-cell { display: table-cell; width: 50%; text-align: center; padding: 0 24px; }
        .firma-linea { border-top: 1px solid #333; margin-top: 36px; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Estado de cuenta</h1>
    <p class="muted">DENS32 Clinica Dental · Generado el {{ $generadoEn->format('d/m/Y H:i') }}</p>

    {{-- Datos del paciente --}}
    <div class="grid">
        <div class="row">
            <div class="cell"><strong>Paciente:</strong> {{ $paciente->nombre_completo }}</div>
            <div class="cell"><strong>DPI:</strong> {{ $paciente->dpi ?: '-' }}</div>
        </div>
        <div class="row">
            <div class="cell"><strong>Telefono:</strong> {{ $paciente->telefono ?: '-' }}</div>
            <div class="cell"><strong>Edad:</strong> {{ $paciente->edad !== null ? $paciente->edad.' anios' : '-' }}</div>
        </div>
    </div>

    {{-- Consulta de referencia (solo cuando la ficha esta anclada a una consulta) --}}
    @if ($consulta)
        <div class="ref">
            <strong>Consulta de referencia:</strong>
            {{ $consulta->fecha?->format('d/m/Y') }}
            @if ($consulta->motivo)
                · {{ $consulta->motivo }}
            @endif
            @if ($consulta->cita && $consulta->cita->startsAt())
                <br>
                <span class="muted">Cita original: {{ $consulta->cita->startsAt()->format('d/m/Y H:i') }}</span>
            @endif
        </div>
    @endif

    {{-- Presupuesto --}}
    <h2>{{ $consulta ? 'Presupuesto de la consulta' : 'Presupuesto general' }}</h2>
    @if ($items->isEmpty())
        <p class="muted">No hay lineas de presupuesto registradas.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Pieza</th>
                    <th>Diagnostico / Tratamiento</th>
                    <th class="num">Cant.</th>
                    <th class="num">P. unitario</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->pieza?->numero ?? '—' }}</td>
                        <td>
                            @if ($item->diagnostico)<strong>{{ $item->diagnostico }}</strong><br>@endif
                            {{ $item->tratamiento }}
                        </td>
                        <td class="num">{{ $item->cantidad }}</td>
                        <td class="num">Q{{ number_format((float) $item->precio_unitario, 2) }}</td>
                        <td class="num">Q{{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="right">Total presupuesto de esta ficha</td>
                    <td class="num">Q{{ number_format((float) $items->sum('subtotal'), 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- Abonos --}}
    <h2>Abonos</h2>
    @if ($abonos->isEmpty())
        <p class="muted">No hay abonos cobrados registrados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Metodo de pago</th>
                    <th>Notas</th>
                    <th class="num">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($abonos as $abono)
                    <tr>
                        <td>{{ optional($abono->fecha_pago ?? $abono->created_at)->format('d/m/Y') }}</td>
                        <td>{{ $abono->metodo_pago ?: '-' }}</td>
                        <td>{{ $abono->notas ?: '-' }}</td>
                        <td class="num">Q{{ number_format((float) $abono->monto, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="right">Total abonado</td>
                    <td class="num">Q{{ number_format($totalAbonado, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- Resumen financiero (saldo del paciente, global) --}}
    <h2>Resumen</h2>
    <table class="resumen">
        <tr>
            <td>Presupuesto general del paciente</td>
            <td class="num">Q{{ number_format($presupuestoGeneral, 2) }}</td>
        </tr>
        <tr>
            <td>Total abonado</td>
            <td class="num">Q{{ number_format($totalAbonado, 2) }}</td>
        </tr>
        <tr class="saldo">
            <td>Saldo actual</td>
            <td class="num">Q{{ number_format($saldoActual, 2) }}</td>
        </tr>
    </table>

    {{-- Observaciones (N por consulta, ordenadas por registro) --}}
    <h2>Observaciones</h2>
    @if ($observaciones->isEmpty())
        <p class="muted">Sin observaciones registradas.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 22%;">Fecha</th>
                    <th>Observacion</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($observaciones as $obs)
                    <tr>
                        <td>
                            {{ optional($obs['consulta_fecha'])->format('d/m/Y') ?? optional($obs['fecha'])->format('d/m/Y') }}
                            @if ($obs['fecha'])
                                <br><span class="muted">{{ $obs['fecha']->format('H:i') }}</span>
                            @endif
                        </td>
                        <td>{{ $obs['descripcion'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Firma fisica --}}
    <div class="firmas grid">
        <div class="row">
            <div class="firma-cell">
                <div class="firma-linea">Firma del paciente</div>
            </div>
            <div class="firma-cell">
                <div class="firma-linea">Firma y sello del profesional</div>
            </div>
        </div>
    </div>
</body>
</html>
