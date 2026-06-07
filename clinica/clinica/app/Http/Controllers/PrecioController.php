<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\TarifaTratamiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrecioController extends Controller
{
    private const ESTADOS_ODONTOGRAMA = [
        'sana',
        'caries',
        'obturada',
        'ausente',
        'extraccion',
        'corona',
        'endodoncia',
    ];

    public function index(): View
    {
        return view('precios.index', [
            'servicios' => Servicio::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion', 'duracion_minutos', 'precio_sugerido', 'activo']),
            'tarifas' => TarifaTratamiento::query()
                ->orderBy('estado_pieza')
                ->get(['id', 'estado_pieza', 'nombre_legible', 'precio_sugerido', 'activo']),
            'estadosOdontograma' => self::ESTADOS_ODONTOGRAMA,
        ]);
    }

    public function updateServicio(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'precio_sugerido' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $servicio->update($validated);

        return response()->json([
            'servicio' => $servicio->refresh(),
        ]);
    }

    public function storeTarifa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'estado_pieza' => ['required', 'string', 'max:40', Rule::unique('tarifas_tratamientos', 'estado_pieza')],
            'nombre_legible' => ['required', 'string', 'max:255'],
            'precio_sugerido' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $tarifa = TarifaTratamiento::create([
            ...$validated,
            'activo' => $validated['activo'] ?? true,
        ]);

        return response()->json(['tarifa' => $tarifa], 201);
    }

    public function updateTarifa(Request $request, TarifaTratamiento $tarifa): JsonResponse
    {
        $validated = $request->validate([
            'estado_pieza' => [
                'required',
                'string',
                'max:40',
                Rule::unique('tarifas_tratamientos', 'estado_pieza')->ignore($tarifa),
            ],
            'nombre_legible' => ['required', 'string', 'max:255'],
            'precio_sugerido' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'activo' => ['required', 'boolean'],
        ]);

        $tarifa->update($validated);

        return response()->json(['tarifa' => $tarifa->refresh()]);
    }

    public function destroyTarifa(TarifaTratamiento $tarifa): JsonResponse
    {
        $tarifa->delete();

        return response()->json(['ok' => true]);
    }
}
