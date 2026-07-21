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

    public function storeServicio(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglasServicio());

        $servicio = Servicio::create([
            ...$validated,
            'activo' => $validated['activo'] ?? true,
        ]);

        return response()->json(['servicio' => $servicio], 201);
    }

    public function updateServicio(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate($this->reglasServicio(paraCrear: false));

        $servicio->update($validated);

        return response()->json([
            'servicio' => $servicio->refresh(),
        ]);
    }

    /**
     * Reglas de validación de un servicio. Al crear, `activo` es opcional
     * (default true); al editar es obligatorio para reflejar el toggle.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglasServicio(bool $paraCrear = true): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'duracion_minutos' => ['required', 'integer', 'min:5', 'max:600'],
            'precio_sugerido' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'activo' => [$paraCrear ? 'nullable' : 'required', 'boolean'],
        ];
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
