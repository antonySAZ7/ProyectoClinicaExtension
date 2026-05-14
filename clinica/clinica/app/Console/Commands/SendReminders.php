<?php

namespace App\Console\Commands;

use App\Mail\CitaReminderMail;
use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminders extends Command
{
    protected $signature = 'reminders:send {--hours=24 : Horas hacia adelante para buscar citas proximas}';

    protected $description = 'Enviar recordatorios automaticos de citas proximas.';

    public function handle(): int
    {
        $whatsApp = app(WhatsAppService::class);
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

            if ($cita->paciente->telefono) {
                $this->sendWhatsAppReminder(
                    $whatsApp,
                    $cita,
                    $cita->paciente->telefono,
                    'recordatorio_paciente',
                    'recordatorio_paciente'
                );
            }

            if (config('services.whatsapp.doctor_number')) {
                $this->sendWhatsAppReminder(
                    $whatsApp,
                    $cita,
                    config('services.whatsapp.doctor_number'),
                    'recordatorio_doctora',
                    'recordatorio_doctora'
                );
            }

            $cita->update([
                'recordatorio_enviado_at' => $now,
            ]);

            $enviados++;
        }

        $this->info("Recordatorios enviados: {$enviados}. Omitidos: {$omitidos}.");

        return self::SUCCESS;
    }

    protected function sendWhatsAppReminder(
        WhatsAppService $whatsApp,
        Cita $cita,
        string $to,
        string $template,
        string $tipo
    ): void {
        $result = $whatsApp->sendTemplate($to, $template, [
            $cita->paciente?->nombre_completo ?? 'Paciente',
            $cita->fecha?->format('d/m/Y'),
            substr((string) $cita->hora, 0, 5),
            $cita->motivo,
        ]);

        NotificacionLog::create([
            'cita_id' => $cita->id,
            'canal' => 'whatsapp',
            'tipo' => $tipo,
            'destinatario' => $to,
            'estado' => $result['skipped'] ? 'omitido' : ($result['ok'] ? 'enviado' : 'error'),
            'payload' => $result,
            'enviado_en' => now(),
        ]);
    }
}
