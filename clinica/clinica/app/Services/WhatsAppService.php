<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendTemplate(string $to, string $template, array $params = []): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalizeRecipient($to),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => [
                    'code' => config('services.whatsapp.template_language', 'es'),
                ],
                'components' => $this->templateComponents($params),
            ],
        ]);
    }

    public function sendText(string $to, string $message): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalizeRecipient($to),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ]);
    }

    public function isConfigured(): bool
    {
        return filled(config('services.whatsapp.phone_id'))
            && filled(config('services.whatsapp.access_token'));
    }

    protected function send(array $payload): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'skipped' => true,
                'status' => null,
                'payload' => $payload,
                'response' => ['message' => 'WhatsApp no configurado.'],
            ];
        }

        $response = Http::withToken(config('services.whatsapp.access_token'))
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint(), $payload);

        return $this->formatResponse($response, $payload);
    }

    protected function endpoint(): string
    {
        $version = config('services.whatsapp.graph_version', 'v23.0');
        $phoneId = config('services.whatsapp.phone_id');

        return "https://graph.facebook.com/{$version}/{$phoneId}/messages";
    }

    protected function templateComponents(array $params): array
    {
        if (empty($params)) {
            return [];
        }

        return [[
            'type' => 'body',
            'parameters' => collect($params)
                ->map(fn ($value) => [
                    'type' => 'text',
                    'text' => (string) $value,
                ])
                ->values()
                ->all(),
        ]];
    }

    protected function formatResponse(Response $response, array $payload): array
    {
        return [
            'ok' => $response->successful(),
            'skipped' => false,
            'status' => $response->status(),
            'payload' => $payload,
            'response' => $response->json() ?? ['body' => $response->body()],
        ];
    }

    protected function normalizeRecipient(string $to): string
    {
        return preg_replace('/[^\d]/', '', $to) ?: $to;
    }
}
