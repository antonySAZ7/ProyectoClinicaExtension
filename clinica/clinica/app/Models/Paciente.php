<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Paciente extends Model
{
    protected $fillable = [
        'user_id',
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

    /**
     * Edad en anios calculada desde la fecha de nacimiento.
     */
    protected function edad(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fecha_nacimiento
                ? Carbon::parse($this->fecha_nacimiento)->age
                : null,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function antecedenteClinico(): HasOne
    {
        return $this->hasOne(AntecedenteClinico::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(Historial::class);
    }

    public function recordatoriosSeguimiento(): HasMany
    {
        return $this->hasMany(RecordatorioSeguimiento::class);
    }
}
