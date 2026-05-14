<?php

use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\DisponibilidadController;
use Illuminate\Support\Facades\Route;

Route::get('/disponibilidad', DisponibilidadController::class)
    ->name('api.disponibilidad');

Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify'])
    ->name('api.whatsapp.webhook.verify');

Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'receive'])
    ->name('api.whatsapp.webhook.receive');
