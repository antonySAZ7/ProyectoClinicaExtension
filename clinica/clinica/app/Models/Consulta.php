<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consulta extends Model
{
    protected $fillable = [
        'paciente_id',
        'user_id',
        'cita_id',
        'fecha',
        'motivo',
        'diagnostico',
        'peso',
        'altura',
        'presion_arterial',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'signos_otros',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'peso' => 'decimal:2',
            'altura' => 'decimal:2',
            'frecuencia_cardiaca' => 'integer',
            'frecuencia_respiratoria' => 'integer',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function observaciones(): HasMany
    {
        return $this->hasMany(Observacion::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    public function piezasDentales(): BelongsToMany
    {
        return $this->belongsToMany(PiezaDental::class, 'consulta_pieza_dental', 'consulta_id', 'pieza_id')
            ->withPivot(['estado', 'observaciones'])
            ->withTimestamps();
    }
}
