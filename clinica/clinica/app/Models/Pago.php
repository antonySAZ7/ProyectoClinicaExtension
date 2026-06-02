<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Pago extends Model implements Auditable
{
    use AuditableTrait;

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_COMPLETADO = 'completado';

    public const ESTADO_PAGADO = 'pagado';

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    protected $fillable = [
        'paciente_id',
        'cita_id',
        'consulta_id',
        'monto',
        'metodo_pago',
        'estado',
        'fecha_pago',
        'notas',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }
}
