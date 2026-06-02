<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\HorarioClinica;
use App\Models\Servicio;
use Illuminate\Support\Carbon;

class AppointmentAvailabilityService
{
    public function availableSlots(string $fecha, ?Servicio $servicio = null): array
    {
        return array_values(array_filter(
            $this->timelineSlots($fecha, $servicio),
            fn (array $slot) => $slot['disponible']
        ));
    }

    public function timelineSlots(string $fecha, ?Servicio $servicio = null): array
    {
        $date = Carbon::parse($fecha)->startOfDay();
        $duration = $servicio?->duracion_minutos ?? 30;
        $step = 60;
        $schedule = HorarioClinica::query()
            ->where('dia_semana', $date->dayOfWeek)
            ->where('activo', true)
            ->first();

        if (! $schedule) {
            return [];
        }

        $start = $date->copy()->setTimeFromTimeString((string) $schedule->hora_apertura);
        $close = $date->copy()->setTimeFromTimeString((string) $schedule->hora_cierre);
        $minHora = $date->isToday() && now()->greaterThan($start) ? $this->ceilToInterval(now(), 15) : null;

        $slots = [];

        for ($cursor = $start->copy(); $cursor->copy()->addMinutes($duration)->lessThanOrEqualTo($close); $cursor->addMinutes($step)) {
            $end = $cursor->copy()->addMinutes($duration);
            $pasado = $minHora && $cursor->lessThan($minHora);
            $libre = ! $pasado && $this->isAvailable($date->toDateString(), $cursor->format('H:i'), $end->format('H:i'));

            $slots[] = [
                'hora' => $cursor->format('H:i'),
                'hora_fin' => $end->format('H:i'),
                'label' => $cursor->format('H:i').' - '.$end->format('H:i'),
                'disponible' => $libre,
                'motivo' => $pasado ? 'pasado' : ($libre ? null : 'ocupado'),
            ];
        }

        return $slots;
    }

    public function isAvailable(string $fecha, string $hora, string $horaFin, ?int $exceptCitaId = null): bool
    {
        if (! $this->fitsClinicSchedule($fecha, $hora, $horaFin)) {
            return false;
        }

        return ! Cita::query()
            ->whereDate('fecha', $fecha)
            ->where('hora', '<', $horaFin)
            ->where('hora_fin', '>', $hora)
            ->whereNotIn('estado', [Cita::ESTADO_CANCELADA, Cita::ESTADO_NO_SHOW])
            ->when($exceptCitaId, fn ($query) => $query->whereKeyNot($exceptCitaId))
            ->exists();
    }

    public function endTimeFor(string $hora, Servicio $servicio): string
    {
        return Carbon::createFromFormat('H:i', $hora)
            ->addMinutes($servicio->duracion_minutos)
            ->format('H:i');
    }

    protected function fitsClinicSchedule(string $fecha, string $hora, string $horaFin): bool
    {
        $date = Carbon::parse($fecha)->startOfDay();
        $schedule = HorarioClinica::query()
            ->where('dia_semana', $date->dayOfWeek)
            ->where('activo', true)
            ->first();

        if (! $schedule) {
            if (HorarioClinica::count() === 0) {
                return true;
            }

            return false;
        }

        $start = $date->copy()->setTimeFromTimeString($hora);
        $end = $date->copy()->setTimeFromTimeString($horaFin);
        $open = $date->copy()->setTimeFromTimeString((string) $schedule->hora_apertura);
        $close = $date->copy()->setTimeFromTimeString((string) $schedule->hora_cierre);

        return $end->greaterThan($start)
            && $start->greaterThanOrEqualTo($open)
            && $end->lessThanOrEqualTo($close);
    }

    protected function ceilToInterval(Carbon $time, int $minutes): Carbon
    {
        $rounded = $time->copy()->second(0)->microsecond(0);
        $remainder = $rounded->minute % $minutes;

        if ($remainder !== 0) {
            $rounded->addMinutes($minutes - $remainder);
        }

        return $rounded;
    }
}
