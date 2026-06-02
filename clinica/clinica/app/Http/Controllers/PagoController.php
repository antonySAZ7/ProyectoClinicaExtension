<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Pago;
use App\Services\PagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    public function store(Request $request, Paciente $paciente, PagoService $service): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'cita_id' => ['nullable', 'exists:citas,id'],
            'consulta_id' => ['nullable', 'exists:consultas,id'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'metodo_pago' => ['required', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in([Pago::ESTADO_COMPLETADO, Pago::ESTADO_PAGADO, Pago::ESTADO_PENDIENTE])],
            'fecha_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        $pago = $service->registrarAbono($paciente, $validated);

        $paciente->load(['consultas.presupuestoItems', 'pagos']);

        $payload = [
            'pago' => $pago,
            'saldo_pendiente' => $paciente->saldo_pendiente,
            'total_pagado' => $paciente->total_pagado,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, 201);
        }

        return back()->with('success', 'Abono registrado correctamente.');
    }
}
