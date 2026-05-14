<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Models\Paciente;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        if ($mode === 'subscribe' && hash_equals((string) config('services.whatsapp.verify_token'), (string) $token)) {
            return response((string) $challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request, WhatsAppService $whatsApp): JsonResponse
    {
        $payload = $request->all();
        $messages = $this->extractMessages($payload);

        foreach ($messages as $message) {
            $from = $message['from'] ?? null;
            $text = trim((string) data_get($message, 'text.body', ''));

            if (! $from || $text === '') {
                continue;
            }

            $cita = $this->findCitaForIncomingNumber($from);
            $normalizedText = Str::of($text)->ascii()->upper()->trim()->toString();
            $estado = 'recibido';
            $tipo = 'mensaje_entrante';

            if ($cita && in_array($normalizedText, ['SI', 'SI CONFIRMO', 'CONFIRMO'], true)) {
                $cita->update(['estado' => Cita::ESTADO_CONFIRMADA]);
                $estado = 'procesado';
                $tipo = 'confirmacion_bidireccional';
            } elseif ($cita && in_array($normalizedText, ['NO', 'CANCELAR', 'CANCELO'], true)) {
                $cita->update(['estado' => Cita::ESTADO_CANCELADA]);
                $estado = 'procesado';
                $tipo = 'cancelacion_bidireccional';
            } else {
                $doctorNumber = config('services.whatsapp.doctor_number');

                if ($doctorNumber) {
                    $whatsApp->sendText($doctorNumber, "Mensaje recibido por WhatsApp de {$from}: {$text}");
                }
            }

            NotificacionLog::create([
                'cita_id' => $cita?->id,
                'canal' => 'whatsapp',
                'tipo' => $tipo,
                'destinatario' => $from,
                'estado' => $estado,
                'payload' => [
                    'message' => $message,
                    'raw_text' => $text,
                ],
                'enviado_en' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    protected function extractMessages(array $payload): array
    {
        $messages = [];

        foreach (data_get($payload, 'entry', []) as $entry) {
            foreach (data_get($entry, 'changes', []) as $change) {
                foreach (data_get($change, 'value.messages', []) as $message) {
                    $messages[] = $message;
                }
            }
        }

        return $messages;
    }

    protected function findCitaForIncomingNumber(string $from): ?Cita
    {
        $normalizedFrom = $this->normalizePhone($from);

        $paciente = Paciente::query()
            ->get()
            ->first(fn (Paciente $paciente) => $this->normalizePhone($paciente->telefono) === $normalizedFrom);

        if (! $paciente) {
            return null;
        }

        return $paciente->citas()
            ->upcoming()
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->first();
    }

    protected function normalizePhone(?string $phone): string
    {
        return preg_replace('/[^\d]/', '', (string) $phone) ?: '';
    }
}
