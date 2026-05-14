<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Cita extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_CONFIRMADA = 'confirmada';

    public const ESTADO_CANCELADA = 'cancelada';

    protected $casts = [
        'fecha' => 'date',
        'recordatorio_enviado_at' => 'datetime',
    ];

    protected $fillable = [
        'paciente_id',
        'servicio_id',
        'fecha',
        'hora',
        'hora_fin',
        'motivo',
        'estado',
        'observaciones',
        'recordatorio_enviado_at',
    ];

    public function scopeUpcoming(Builder $query): Builder
    {
        $today = today()->toDateString();
        $currentTime = now()->format('H:i:s');

        return $query->where(function (Builder $query) use ($today, $currentTime) {
            $query->whereDate('fecha', '>', $today)
                ->orWhere(function (Builder $query) use ($today, $currentTime) {
                    $query->whereDate('fecha', $today)
                        ->whereTime('hora', '>=', $currentTime);
                });
        });
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function startsAt(): ?Carbon
    {
        if (! $this->fecha || ! $this->hora) {
            return null;
        }

        return $this->fecha->copy()->setTimeFromTimeString((string) $this->hora);
    }

    public function endsAt(): ?Carbon
    {
        if (! $this->fecha || ! $this->hora_fin) {
            return null;
        }

        return $this->fecha->copy()->setTimeFromTimeString((string) $this->hora_fin);
    }

    public function isFuture(): bool
    {
        return $this->startsAt()?->greaterThanOrEqualTo(now()) ?? false;
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class);
    }

    public function recordatoriosSeguimiento(): HasMany
    {
        return $this->hasMany(RecordatorioSeguimiento::class);
    }
}
