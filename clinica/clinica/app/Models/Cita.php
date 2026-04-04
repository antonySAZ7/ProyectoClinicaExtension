<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    protected $casts = [
        'fecha' => 'date',
    ];

    protected $fillable = [
        'paciente_id',
        'fecha',
        'hora',
        'motivo',
        'estado',
        'observaciones',
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

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class);
    }
}
