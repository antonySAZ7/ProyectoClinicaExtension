<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioClinica extends Model
{
    protected $table = 'horarios_clinica';

    protected $fillable = [
        'dia_semana',
        'hora_apertura',
        'hora_cierre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
