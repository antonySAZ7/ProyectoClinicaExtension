<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Tratamiento extends Model implements Auditable
{
    use AuditableTrait;

    public const ESTADO_EN_PROGRESO = 'en_progreso';

    public const ESTADO_FINALIZADO = 'finalizado';

    public const ESTADO_SUSPENDIDO = 'suspendido';

    protected $fillable = [
        'paciente_id',
        'pieza_id',
        'user_id',
        'nombre',
        'descripcion',
        'estado',
        'fecha_inicio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function pieza(): BelongsTo
    {
        return $this->belongsTo(PiezaDental::class, 'pieza_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fases(): HasMany
    {
        return $this->hasMany(FaseTratamiento::class)->orderBy('orden')->orderBy('fecha')->orderBy('id');
    }

    public function scopeActivosPrimero(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE estado WHEN 'en_progreso' THEN 0 WHEN 'suspendido' THEN 1 ELSE 2 END")
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id');
    }

    public function isFinalizado(): bool
    {
        return $this->estado === self::ESTADO_FINALIZADO;
    }
}
