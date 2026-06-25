<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PiezaDental extends Model
{
    public const TIPO_PERMANENTE = 'permanente';
    public const TIPO_TEMPORAL = 'temporal';

    protected $table = 'piezas_dentales';

    protected $fillable = [
        'numero',
        'nombre',
        'cuadrante',
        'posicion',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'cuadrante' => 'integer',
            'posicion' => 'integer',
        ];
    }

    public function scopeForTipo($query, ?string $tipo)
    {
        if (! in_array($tipo, [self::TIPO_PERMANENTE, self::TIPO_TEMPORAL], true)) {
            return $query;
        }

        return $query->where('tipo', $tipo);
    }

    public function numeroVisible(): int
    {
        if ($this->tipo === self::TIPO_TEMPORAL) {
            return $this->numero;
        }

        return self::MAPA_FDI_A_UNIVERSAL[$this->numero] ?? $this->numero;
    }

    public function numeroReferencia(): string
    {
        return 'FDI '.$this->numero;
    }

    public function tipoLegible(): string
    {
        return $this->tipo === self::TIPO_TEMPORAL ? 'Niño' : 'Adulto';
    }

    private const MAPA_FDI_A_UNIVERSAL = [
        18 => 1, 17 => 2, 16 => 3, 15 => 4, 14 => 5, 13 => 6, 12 => 7, 11 => 8,
        21 => 9, 22 => 10, 23 => 11, 24 => 12, 25 => 13, 26 => 14, 27 => 15, 28 => 16,
        38 => 17, 37 => 18, 36 => 19, 35 => 20, 34 => 21, 33 => 22, 32 => 23, 31 => 24,
        41 => 25, 42 => 26, 43 => 27, 44 => 28, 45 => 29, 46 => 30, 47 => 31, 48 => 32,
    ];

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
