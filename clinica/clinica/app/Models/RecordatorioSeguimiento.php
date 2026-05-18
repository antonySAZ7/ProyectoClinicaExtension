<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordatorioSeguimiento extends Model
{
    public const MODO_INTERVALO = 'intervalo';

    public const MODO_PERSONALIZADO = 'personalizado';

    protected $table = 'recordatorios_seguimiento';

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'activo',
        'modo',
        'titulo',
        'intervalo_meses',
        'fecha_objetivo',
        'dias_antes',
        'mensaje',
        'fechas_enviadas',
        'ultimo_envio_at',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'intervalo_meses' => 'integer',
            'fecha_objetivo' => 'date',
            'dias_antes' => 'array',
            'fechas_enviadas' => 'array',
            'ultimo_envio_at' => 'datetime',
        ];
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function displayTitle(): string
    {
        return $this->titulo
            ?: ($this->cita?->servicio?->nombre ?? 'Seguimiento dental');
    }

    public function hasBeenSentFor(string $date): bool
    {
        return in_array($date, $this->fechas_enviadas ?? [], true);
    }

    public function markSentFor(string $date): void
    {
        $sentDates = $this->fechas_enviadas ?? [];

        if (! in_array($date, $sentDates, true)) {
            $sentDates[] = $date;
        }

        $this->update([
            'fechas_enviadas' => array_values($sentDates),
            'ultimo_envio_at' => now(),
        ]);
    }
}
