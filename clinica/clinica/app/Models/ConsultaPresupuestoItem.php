<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ConsultaPresupuestoItem extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'consulta_presupuesto_items';

    protected $fillable = [
        'consulta_id',
        'pieza_id',
        'diagnostico',
        'tratamiento',
        'precio_unitario',
        'cantidad',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'cantidad' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ConsultaPresupuestoItem $item): void {
            $item->subtotal = round((float) $item->precio_unitario * max(1, (int) $item->cantidad), 2);
        });
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function pieza(): BelongsTo
    {
        return $this->belongsTo(PiezaDental::class, 'pieza_id');
    }
}
