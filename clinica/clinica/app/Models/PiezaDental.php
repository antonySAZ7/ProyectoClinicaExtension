<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PiezaDental extends Model
{
    protected $table = 'piezas_dentales';

    protected $fillable = [
        'numero',
        'nombre',
        'cuadrante',
        'posicion',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'cuadrante' => 'integer',
            'posicion' => 'integer',
        ];
    }

    public function consultas(): BelongsToMany
    {
        return $this->belongsToMany(Consulta::class, 'consulta_pieza_dental', 'pieza_id', 'consulta_id')
            ->withPivot(['estado', 'observaciones'])
            ->withTimestamps();
    }

    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'pieza_id');
    }
}
