<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
        'nombre_completo',
        'dpi',
        'fecha_nacimiento',
        'telefono',
        'correo',
        'direccion',
        'sexo',
        'estado_civil',
        'ocupacion',
    ];
}
