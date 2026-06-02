<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class TarifaTratamiento extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'tarifas_tratamientos';

    protected $fillable = [
        'estado_pieza',
        'nombre_legible',
        'precio_sugerido',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_sugerido' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
