<?php

namespace App\Services;

use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Models\TarifaTratamiento;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PresupuestoService
{
    public function createItem(Consulta $consulta, array $data): ConsultaPresupuestoItem
    {
        $this->assertItemsAreEditable($consulta);

        return $consulta->presupuestoItems()->create($this->normalizeItemData($data));
    }

    public function updateItem(ConsultaPresupuestoItem $item, array $data): ConsultaPresupuestoItem
    {
        $item->loadMissing('consulta');
        $this->assertItemsAreEditable($item->consulta);

        $item->update($this->normalizeItemData($data, partial: true));

        return $item->refresh();
    }

    public function deleteItem(ConsultaPresupuestoItem $item): void
    {
        $item->loadMissing('consulta');
        $this->assertItemsAreEditable($item->consulta);

        $item->delete();
    }

    public function acceptBudget(Consulta $consulta): Consulta
    {
        if (! $consulta->presupuesto_aceptado_en) {
            $consulta->update([
                'presupuesto_aceptado_en' => now(),
            ]);
        }

        return $consulta->refresh();
    }

    public function totalForConsulta(Consulta $consulta): float
    {
        return round((float) $consulta->presupuestoItems()->sum('subtotal'), 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function suggestItemsFromOdontograma(Consulta $consulta): array
    {
        $consulta->loadMissing('piezasDentales');

        $tarifas = TarifaTratamiento::query()
            ->where('activo', true)
            ->get()
            ->keyBy('estado_pieza');

        return $consulta->piezasDentales
            ->filter(fn ($pieza) => ($pieza->pivot?->estado ?? 'sana') !== 'sana')
            ->map(function ($pieza) use ($tarifas) {
                $estado = $pieza->pivot?->estado ?? 'sana';
                $tarifa = $tarifas->get($estado);
                $nombreTratamiento = $tarifa?->nombre_legible ?? Str::headline(str_replace('_', ' ', $estado));
                $precio = (float) ($tarifa?->precio_sugerido ?? 0);

                return [
                    'pieza_id' => $pieza->id,
                    'pieza_numero' => $pieza->numero,
                    'diagnostico' => $nombreTratamiento,
                    'tratamiento' => $nombreTratamiento,
                    'precio_unitario' => round($precio, 2),
                    'cantidad' => 1,
                    'subtotal' => round($precio, 2),
                    'estado_pieza' => $estado,
                    'observaciones_odontograma' => $pieza->pivot?->observaciones,
                ];
            })
            ->values()
            ->all();
    }

    public function assertItemsAreEditable(Consulta $consulta): void
    {
        if ($consulta->presupuesto_aceptado_en) {
            throw ValidationException::withMessages([
                'presupuesto' => 'El presupuesto ya fue aceptado; sus items no se pueden modificar.',
            ]);
        }
    }

    protected function normalizeItemData(array $data, bool $partial = false): array
    {
        $normalized = [];

        foreach (['pieza_id', 'diagnostico', 'tratamiento', 'precio_unitario', 'cantidad'] as $field) {
            if (array_key_exists($field, $data)) {
                $normalized[$field] = $data[$field];
            }
        }

        if (! $partial || array_key_exists('cantidad', $normalized)) {
            $normalized['cantidad'] = max(1, (int) ($normalized['cantidad'] ?? 1));
        }

        if (array_key_exists('precio_unitario', $normalized)) {
            $normalized['precio_unitario'] = round(max(0, (float) $normalized['precio_unitario']), 2);
        }

        return $normalized;
    }
}
