<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisponibilidadController extends Controller
{
    public function __invoke(Request $request, AppointmentAvailabilityService $availability): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'servicio_id' => ['nullable', Rule::exists('servicios', 'id')->where('activo', true)],
        ]);

        $servicio = isset($validated['servicio_id'])
            ? Servicio::find($validated['servicio_id'])
            : null;

        return response()->json([
            'fecha' => $validated['fecha'],
            'servicio_id' => $servicio?->id,
            'duracion_minutos' => $servicio?->duracion_minutos ?? 30,
            'bloques' => $availability->availableSlots($validated['fecha'], $servicio),
        ]);
    }
}
