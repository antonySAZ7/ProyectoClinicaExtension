<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class FaseTratamiento extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'fases_tratamiento';

    protected $fillable = [
        'tratamiento_id',
        'consulta_id',
        'user_id',
        'descripcion',
        'fecha',
        'completada',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'completada' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
