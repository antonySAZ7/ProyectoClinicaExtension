<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_minutos',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'duracion_minutos' => 'integer',
        ];
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
