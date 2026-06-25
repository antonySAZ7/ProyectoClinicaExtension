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

    private const TIPOS = [
        PiezaDental::TIPO_PERMANENTE,
        PiezaDental::TIPO_TEMPORAL,
        'all',
    ];

    public function index(Request $request, Consulta $consulta): JsonResponse
    {
        $this->authorize('view', $consulta);

        $consulta->load('piezasDentales');
        $piezasConsulta = $consulta->piezasDentales->keyBy('id');
        $tipo = $request->string('tipo')->toString();
        $tipo = in_array($tipo, self::TIPOS, true) ? $tipo : PiezaDental::TIPO_PERMANENTE;

        $piezasQuery = PiezaDental::query()
            ->orderBy('cuadrante')
            ->orderBy('posicion')
            ->orderBy('numero');

        if ($tipo !== 'all') {
            $piezasQuery->forTipo($tipo);
        }

        $piezas = $piezasQuery
            ->get()
            ->map(function (PiezaDental $pieza) use ($piezasConsulta) {
                $registrada = $piezasConsulta->get($pieza->id);

                return [
                    'id' => $pieza->id,
                    'numero' => $pieza->numeroVisible(),
                    'numero_fdi' => $pieza->numero,
                    'numero_referencia' => $pieza->numeroReferencia(),
                    'nombre' => $pieza->nombre,
                    'cuadrante' => $pieza->cuadrante,
                    'posicion' => $pieza->posicion,
                    'tipo' => $pieza->tipo,
                    'tipo_legible' => $pieza->tipoLegible(),
                    'estado' => $registrada?->pivot?->estado ?? 'sana',
                    'observaciones' => $registrada?->pivot?->observaciones,
                ];
            });

        return response()->json([
            'consulta_id' => $consulta->id,
            'tipo' => $tipo,
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
