<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'paciente_id',
        'cita_id',
        'monto',
        'metodo_pago',
        'estado',
        'fecha_pago',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }
}
