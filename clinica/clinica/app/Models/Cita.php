<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $fillable = [
        'paciente_id',
        'fecha',
        'hora',
        'motivo',
        'estado',
        'observaciones',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function pago()
    {
        return $this->hasOne(Pago::class);
    }
}
