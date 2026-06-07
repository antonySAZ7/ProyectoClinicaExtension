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

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

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

    protected function presupuestoTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('consultas')) {
                    return round((float) $this->consultas->sum(
                        fn (Consulta $consulta) => $consulta->presupuesto_total
                    ), 2);
                }

                return round((float) ConsultaPresupuestoItem::query()
                    ->whereHas('consulta', fn ($query) => $query->where('paciente_id', $this->id))
                    ->sum('subtotal'), 2);
            },
        );
    }

    protected function totalPagado(): Attribute
    {
        return Attribute::make(
            get: function () {
                $estadosCompletados = [Pago::ESTADO_COMPLETADO, Pago::ESTADO_PAGADO];

                if ($this->relationLoaded('pagos')) {
                    return round((float) $this->pagos
                        ->whereIn('estado', $estadosCompletados)
                        ->sum('monto'), 2);
                }

                return round((float) $this->pagos()
                    ->whereIn('estado', $estadosCompletados)
                    ->sum('monto'), 2);
            },
        );
    }

    protected function saldoPendiente(): Attribute
    {
        return Attribute::make(
            get: fn () => round(max(0, (float) $this->presupuesto_total - (float) $this->total_pagado), 2),
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
