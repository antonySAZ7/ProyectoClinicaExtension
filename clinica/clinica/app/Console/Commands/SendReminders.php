<?php

namespace App\Console\Commands;

use App\Mail\CitaReminderMail;
use App\Models\Cita;
use App\Models\NotificacionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminders extends Command
{
    protected $signature = 'reminders:send {--hours=24 : Horas hacia adelante para buscar citas proximas}';

    protected $description = 'Enviar recordatorios automaticos de citas proximas.';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $now = now();
        $until = $now->copy()->addHours($hours);

        $citas = Cita::query()
            ->with('paciente')
            ->whereNull('recordatorio_enviado_at')
            ->whereIn('estado', [
                Cita::ESTADO_PENDIENTE,
                Cita::ESTADO_CONFIRMADA,
            ])
            ->whereDate('fecha', '>=', $now->toDateString())
            ->whereDate('fecha', '<=', $until->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        $enviados = 0;
        $omitidos = 0;

        foreach ($citas as $cita) {
            $inicio = $cita->startsAt();

            if (! $inicio || $inicio->lessThan($now) || $inicio->greaterThan($until)) {
                continue;
            }

            if (! $cita->paciente || ! $cita->paciente->correo) {
                $omitidos++;
                $this->warn("Cita {$cita->id} omitida: paciente sin correo.");

                continue;
            }

            Mail::to($cita->paciente->correo)->send(new CitaReminderMail($cita));

            NotificacionLog::create([
                'cita_id' => $cita->id,
                'canal' => 'email',
                'tipo' => 'recordatorio_cita',
                'destinatario' => $cita->paciente->correo,
                'estado' => 'enviado',
                'payload' => [
                    'fecha' => $cita->fecha?->toDateString(),
                    'hora' => substr((string) $cita->hora, 0, 5),
                    'motivo' => $cita->motivo,
                ],
                'enviado_en' => now(),
            ]);

            $cita->update([
                'recordatorio_enviado_at' => $now,
            ]);

            $enviados++;
        }

        $this->info("Recordatorios enviados: {$enviados}. Omitidos: {$omitidos}.");

        return self::SUCCESS;
    }
}
