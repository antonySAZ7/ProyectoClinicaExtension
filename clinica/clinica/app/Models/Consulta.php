<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Consulta extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'cita_id',
        'consulta_origen_id',
        'fecha',
        'motivo',
        'diagnostico',
        'peso',
        'altura',
        'presion_arterial',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'signos_otros',
        'presupuesto_aceptado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'peso' => 'decimal:2',
            'altura' => 'decimal:2',
            'frecuencia_cardiaca' => 'integer',
            'frecuencia_respiratoria' => 'integer',
            'presupuesto_aceptado_en' => 'datetime',
        ];
    }

    protected function presupuestoTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('presupuestoItems')) {
                    return round((float) $this->presupuestoItems->sum('subtotal'), 2);
                }

                return round((float) $this->presupuestoItems()->sum('subtotal'), 2);
            },
        );
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

    public function consultaOrigen(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_origen_id');
    }

    public function consultasSeguimiento(): HasMany
    {
        return $this->hasMany(Consulta::class, 'consulta_origen_id');
    }

    public function observaciones(): HasMany
    {
        return $this->hasMany(Observacion::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    public function presupuestoItems(): HasMany
    {
        return $this->hasMany(ConsultaPresupuestoItem::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function piezasDentales(): BelongsToMany
    {
        return $this->belongsToMany(PiezaDental::class, 'consulta_pieza_dental', 'consulta_id', 'pieza_id')
            ->withPivot(['estado', 'observaciones'])
            ->withTimestamps();
    }

    public function fasesTratamiento(): HasMany
    {
        return $this->hasMany(FaseTratamiento::class);
    }
}
