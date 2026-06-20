<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Services\PresupuestoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PresupuestoItemController extends Controller
{
    public function store(Request $request, Consulta $consulta, PresupuestoService $service): JsonResponse|RedirectResponse
    {
        $this->authorize('create', [ConsultaPresupuestoItem::class, $consulta]);
        $validated = $request->validate($this->rules());
        $item = $service->createItem($consulta, $validated);

        return $this->respond($request, [
            'item' => $item->load('pieza'),
            'presupuesto_total' => $service->totalForConsulta($consulta),
        ], 'Item de presupuesto agregado.');
    }

    public function update(
        Request $request,
        Consulta $consulta,
        ConsultaPresupuestoItem $item,
        PresupuestoService $service
    ): JsonResponse|RedirectResponse {
        $this->ensureItemBelongsToConsulta($consulta, $item);
        $this->authorize('update', $item);
        $validated = $request->validate($this->rules());
        $item = $service->updateItem($item, $validated);

        return $this->respond($request, [
            'item' => $item->load('pieza'),
            'presupuesto_total' => $service->totalForConsulta($consulta),
        ], 'Item de presupuesto actualizado.');
    }

    public function destroy(
        Request $request,
        Consulta $consulta,
        ConsultaPresupuestoItem $item,
        PresupuestoService $service
    ): JsonResponse|RedirectResponse {
        $this->ensureItemBelongsToConsulta($consulta, $item);
        $this->authorize('delete', $item);
        $service->deleteItem($item);

        return $this->respond($request, [
            'ok' => true,
            'presupuesto_total' => $service->totalForConsulta($consulta),
        ], 'Item de presupuesto eliminado.');
    }

    public function suggest(Consulta $consulta, PresupuestoService $service): JsonResponse
    {
        $this->authorize('viewSuggestions', [ConsultaPresupuestoItem::class, $consulta]);

        return response()->json([
            'items' => $service->suggestItemsFromOdontograma($consulta),
        ]);
    }

    public function accept(Request $request, Consulta $consulta, PresupuestoService $service): JsonResponse|RedirectResponse
    {
        $this->authorize('accept', [ConsultaPresupuestoItem::class, $consulta]);
        $consulta = $service->acceptBudget($consulta);

        return $this->respond($request, [
            'consulta_id' => $consulta->id,
            'presupuesto_aceptado_en' => $consulta->presupuesto_aceptado_en?->toIso8601String(),
        ], 'Presupuesto marcado como aceptado.');
    }

    protected function rules(): array
    {
        return [
            'pieza_id' => ['nullable', 'exists:piezas_dentales,id'],
            'diagnostico' => ['required', 'string', 'max:255'],
            'tratamiento' => ['required', 'string', 'max:255'],
            'precio_unitario' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    protected function ensureItemBelongsToConsulta(Consulta $consulta, ConsultaPresupuestoItem $item): void
    {
        abort_unless($item->consulta_id === $consulta->id, 404);
    }

    protected function respond(Request $request, array $payload, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $message);
    }
}
