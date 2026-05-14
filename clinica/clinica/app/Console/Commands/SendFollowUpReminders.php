<?php

namespace App\Console\Commands;

use App\Mail\SeguimientoReminderMail;
use App\Models\NotificacionLog;
use App\Models\RecordatorioSeguimiento;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFollowUpReminders extends Command
{
    protected $signature = 'followups:send';

    protected $description = 'Enviar recordatorios de seguimiento preventivo para que pacientes agenden una nueva cita.';

    public function handle(WhatsAppService $whatsApp): int
    {
        $today = today();
        $sent = 0;
        $skipped = 0;

        $recordatorios = RecordatorioSeguimiento::query()
            ->with(['paciente', 'cita.servicio'])
            ->where('activo', true)
            ->whereDate('fecha_objetivo', '>=', $today->toDateString())
            ->get();

        foreach ($recordatorios as $recordatorio) {
            $sendDateKey = $today->toDateString();
            $daysUntilTarget = $today->diffInDays($recordatorio->fecha_objetivo, false);
            $configuredDays = collect($recordatorio->dias_antes ?? [1, 0])
                ->map(fn ($day) => (int) $day)
                ->unique()
                ->values();

            if (! $configuredDays->contains($daysUntilTarget) || $recordatorio->hasBeenSentFor($sendDateKey)) {
                continue;
            }

            if (! $recordatorio->paciente?->correo) {
                $skipped++;
                $this->warn("Recordatorio {$recordatorio->id} omitido: paciente sin correo.");

                continue;
            }

            Mail::to($recordatorio->paciente->correo)->send(new SeguimientoReminderMail($recordatorio));

            NotificacionLog::create([
                'cita_id' => $recordatorio->cita_id,
                'canal' => 'email',
                'tipo' => 'recordatorio_seguimiento',
                'destinatario' => $recordatorio->paciente->correo,
                'estado' => 'enviado',
                'payload' => [
                    'recordatorio_id' => $recordatorio->id,
                    'fecha_objetivo' => $recordatorio->fecha_objetivo?->toDateString(),
                    'fecha_envio' => $sendDateKey,
                ],
                'enviado_en' => now(),
            ]);

            if ($recordatorio->paciente->telefono) {
                $result = $whatsApp->sendTemplate(
                    $recordatorio->paciente->telefono,
                    config('services.whatsapp.followup_template', 'recordatorio_seguimiento'),
                    [
                        $recordatorio->paciente->nombre_completo,
                        $recordatorio->cita?->servicio?->nombre ?? 'seguimiento dental',
                        $recordatorio->fecha_objetivo?->format('d/m/Y'),
                        $this->messageFor($recordatorio),
                    ]
                );

                NotificacionLog::create([
                    'cita_id' => $recordatorio->cita_id,
                    'canal' => 'whatsapp',
                    'tipo' => 'recordatorio_seguimiento',
                    'destinatario' => $recordatorio->paciente->telefono,
                    'estado' => $result['skipped'] ? 'omitido' : ($result['ok'] ? 'enviado' : 'error'),
                    'payload' => $result + [
                        'recordatorio_id' => $recordatorio->id,
                        'fecha_objetivo' => $recordatorio->fecha_objetivo?->toDateString(),
                        'fecha_envio' => $sendDateKey,
                    ],
                    'enviado_en' => now(),
                ]);
            }

            $recordatorio->markSentFor($sendDateKey);
            $sent++;
        }

        $this->info("Recordatorios de seguimiento enviados: {$sent}. Omitidos: {$skipped}.");

        return self::SUCCESS;
    }

    protected function messageFor(RecordatorioSeguimiento $recordatorio): string
    {
        if ($recordatorio->mensaje) {
            return $recordatorio->mensaje;
        }

        $service = $recordatorio->cita?->servicio?->nombre ?? 'seguimiento dental';
        $date = $recordatorio->fecha_objetivo?->format('d/m/Y');

        return "Hola {$recordatorio->paciente->nombre_completo}, te recordamos que ya corresponde agendar tu {$service}. Fecha sugerida: {$date}. Puedes responder para coordinar tu cita.";
    }
}
