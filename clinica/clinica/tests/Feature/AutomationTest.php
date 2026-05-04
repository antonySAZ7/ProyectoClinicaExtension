<?php

use App\Mail\CitaReminderMail;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

test('reminders command sends emails for upcoming appointments only once', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 4, 10, 0, 0));
    Mail::fake();

    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Recordatorio',
        'dpi' => '1000000000300',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0300',
        'correo' => 'recordatorio@example.com',
        'direccion' => 'Zona 1',
    ]);

    $citaProxima = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addHours(3)->toDateString(),
        'hora' => now()->copy()->addHours(3)->format('H:i'),
        'motivo' => 'Control proximo',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addHours(26)->toDateString(),
        'hora' => now()->copy()->addHours(26)->format('H:i'),
        'motivo' => 'Fuera de ventana',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addHours(4)->toDateString(),
        'hora' => now()->copy()->addHours(4)->format('H:i'),
        'motivo' => 'Cita cancelada',
        'estado' => Cita::ESTADO_CANCELADA,
    ]);

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addHours(5)->toDateString(),
        'hora' => now()->copy()->addHours(5)->format('H:i'),
        'motivo' => 'Ya notificada',
        'estado' => Cita::ESTADO_CONFIRMADA,
        'recordatorio_enviado_at' => now()->copy()->subHour(),
    ]);

    $this->artisan('reminders:send')
        ->expectsOutput('Recordatorios enviados: 1. Omitidos: 0.')
        ->assertSuccessful();

    Mail::assertSent(CitaReminderMail::class, function (CitaReminderMail $mail) use ($citaProxima) {
        return $mail->cita->is($citaProxima);
    });
    Mail::assertSent(CitaReminderMail::class, 1);

    expect($citaProxima->refresh()->recordatorio_enviado_at)->not->toBeNull();

    $this->artisan('reminders:send')
        ->expectsOutput('Recordatorios enviados: 0. Omitidos: 0.')
        ->assertSuccessful();

    Mail::assertSent(CitaReminderMail::class, 1);

    Carbon::setTestNow();
});

test('patient can confirm their own upcoming appointment', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 4, 10, 0, 0));

    $user = User::factory()->paciente()->create();
    $paciente = Paciente::create([
        'user_id' => $user->id,
        'nombre_completo' => 'Paciente Confirmacion',
        'dpi' => '1000000000301',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0301',
        'correo' => 'confirmacion@example.com',
        'direccion' => 'Zona 2',
    ]);

    $cita = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addDay()->toDateString(),
        'hora' => '09:00',
        'motivo' => 'Limpieza dental',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    $this->actingAs($user)
        ->post(route('citas.confirmar', $cita, false))
        ->assertRedirect(route('portal', absolute: false))
        ->assertSessionHas('success', 'Tu cita fue confirmada correctamente.');

    expect($cita->refresh()->estado)->toBe(Cita::ESTADO_CONFIRMADA);

    Carbon::setTestNow();
});

test('patient can not confirm past cancelled or another patient appointments', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 4, 10, 0, 0));

    $user = User::factory()->paciente()->create();
    $otherUser = User::factory()->paciente()->create();

    $paciente = Paciente::create([
        'user_id' => $user->id,
        'nombre_completo' => 'Paciente Seguridad',
        'dpi' => '1000000000302',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0302',
        'correo' => 'seguridad@example.com',
        'direccion' => 'Zona 3',
    ]);

    $pacienteAjeno = Paciente::create([
        'user_id' => $otherUser->id,
        'nombre_completo' => 'Paciente Ajeno',
        'dpi' => '1000000000303',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0303',
        'correo' => 'ajeno@example.com',
        'direccion' => 'Zona 4',
    ]);

    $citaPasada = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->subDay()->toDateString(),
        'hora' => '09:00',
        'motivo' => 'Pasada',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    $citaCancelada = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addDay()->toDateString(),
        'hora' => '09:00',
        'motivo' => 'Cancelada',
        'estado' => Cita::ESTADO_CANCELADA,
    ]);

    $citaAjena = Cita::create([
        'paciente_id' => $pacienteAjeno->id,
        'fecha' => now()->copy()->addDay()->toDateString(),
        'hora' => '09:00',
        'motivo' => 'Ajena',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    $this->actingAs($user)
        ->post(route('citas.confirmar', $citaPasada, false))
        ->assertRedirect(route('portal', absolute: false))
        ->assertSessionHas('error', 'No puedes confirmar una cita pasada.');

    $this->actingAs($user)
        ->post(route('citas.confirmar', $citaCancelada, false))
        ->assertRedirect(route('portal', absolute: false))
        ->assertSessionHas('error', 'No puedes confirmar una cita cancelada.');

    $this->actingAs($user)
        ->post(route('citas.confirmar', $citaAjena, false))
        ->assertForbidden();

    expect($citaPasada->refresh()->estado)->toBe(Cita::ESTADO_PENDIENTE);
    expect($citaCancelada->refresh()->estado)->toBe(Cita::ESTADO_CANCELADA);
    expect($citaAjena->refresh()->estado)->toBe(Cita::ESTADO_PENDIENTE);

    Carbon::setTestNow();
});
