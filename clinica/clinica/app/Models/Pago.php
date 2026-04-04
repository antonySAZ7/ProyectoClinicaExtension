<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    protected $fillable = [
        'paciente_id',
        'cita_id',
        'monto',
        'metodo_pago',
        'estado',
        'fecha_pago',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }
}
