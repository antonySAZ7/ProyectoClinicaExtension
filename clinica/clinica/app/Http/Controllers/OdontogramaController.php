<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\PiezaDental;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OdontogramaController extends Controller
{
    private const ESTADOS = [
        'sana',
        'caries',
        'obturada',
        'ausente',
        'extraccion',
        'corona',
        'endodoncia',
    ];

    public function index(Request $request, Consulta $consulta): JsonResponse
    {
        $this->authorize('view', $consulta);

        $consulta->load('piezasDentales');
        $piezasConsulta = $consulta->piezasDentales->keyBy('id');

        $piezas = PiezaDental::query()
            ->orderBy('cuadrante')
            ->orderBy('posicion')
            ->get()
            ->map(function (PiezaDental $pieza) use ($piezasConsulta) {
                $registrada = $piezasConsulta->get($pieza->id);

                return [
                    'id' => $pieza->id,
                    'numero' => $pieza->numero,
                    'nombre' => $pieza->nombre,
                    'cuadrante' => $pieza->cuadrante,
                    'posicion' => $pieza->posicion,
                    'estado' => $registrada?->pivot?->estado ?? 'sana',
                    'observaciones' => $registrada?->pivot?->observaciones,
                ];
            });

        return response()->json([
            'consulta_id' => $consulta->id,
            'piezas' => $piezas,
        ]);
    }

    public function store(Request $request, Consulta $consulta): JsonResponse
    {
        $this->authorize('manage', $consulta);

        $validated = $request->validate([
            'piezas' => ['required', 'array'],
            'piezas.*.pieza_id' => ['required', 'exists:piezas_dentales,id'],
            'piezas.*.estado' => ['required', Rule::in(self::ESTADOS)],
            'piezas.*.observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated['piezas'] as $pieza) {
            $consulta->piezasDentales()->syncWithoutDetaching([
                $pieza['pieza_id'] => [
                    'estado' => $pieza['estado'],
                    'observaciones' => $pieza['observaciones'] ?? null,
                ],
            ]);
        }

        return $this->index($request, $consulta);
    }

    public function update(Request $request, Consulta $consulta, PiezaDental $pieza): JsonResponse
    {
        $this->authorize('manage', $consulta);

        $validated = $request->validate([
            'estado' => ['required', Rule::in(self::ESTADOS)],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $consulta->piezasDentales()->syncWithoutDetaching([
            $pieza->id => [
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?? null,
            ],
        ]);

        return $this->index($request, $consulta);
    }

    public function destroy(Consulta $consulta, PiezaDental $pieza): JsonResponse
    {
        $this->authorize('manage', $consulta);

        $consulta->piezasDentales()->detach($pieza->id);

        return response()->json(['ok' => true]);
    }
}
