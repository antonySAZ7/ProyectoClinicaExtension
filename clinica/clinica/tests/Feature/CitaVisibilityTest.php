<?php

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Carbon;

test('admin upcoming appointments list excludes appointments from earlier today', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 4, 10, 0, 0));

    $admin = User::factory()->create();
    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Demo',
        'dpi' => '1000000000001',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-1111',
        'correo' => 'paciente-demo@example.com',
        'direccion' => 'Zona 1',
    ]);

    $horaPasada = now()->subHour()->format('H:i');
    $horaVigente = now()->addHour()->format('H:i');

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->toDateString(),
        'hora' => $horaPasada,
        'motivo' => 'Cita pasada',
        'estado' => 'pendiente',
    ]);

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->toDateString(),
        'hora' => $horaVigente,
        'motivo' => 'Cita vigente',
        'estado' => 'pendiente',
    ]);

    $response = $this->actingAs($admin)->get('/citas');

    $response->assertOk();
    $response->assertDontSee($horaPasada);
    $response->assertSee($horaVigente);

    Carbon::setTestNow();
});

test('patient portal excludes appointments from earlier today', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 4, 10, 0, 0));

    $user = User::factory()->paciente()->create();
    $paciente = Paciente::create([
        'user_id' => $user->id,
        'nombre_completo' => 'Paciente Portal',
        'dpi' => '1000000000002',
        'fecha_nacimiento' => '1992-02-02',
        'telefono' => '5555-2222',
        'correo' => 'paciente-portal@example.com',
        'direccion' => 'Zona 2',
    ]);

    $horaPasada = now()->subHour()->format('H:i');
    $horaVigente = now()->addHour()->format('H:i');

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->toDateString(),
        'hora' => $horaPasada,
        'motivo' => 'Control ya pasado',
        'estado' => 'pendiente',
    ]);

    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->toDateString(),
        'hora' => $horaVigente,
        'motivo' => 'Control vigente',
        'estado' => 'confirmada',
    ]);

    $response = $this->actingAs($user)->get('/portal');

    $response->assertOk();
    $response->assertDontSee('Control ya pasado');
    $response->assertSee('Control vigente');

    Carbon::setTestNow();
});
