<?php

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Carbon;

function citaValidationPaciente(): Paciente
{
    return Paciente::create([
        'nombre_completo' => 'Paciente Validacion',
        'dpi' => '1000000000501',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0501',
        'correo' => 'validacion-cita@example.com',
        'direccion' => 'Zona 4',
    ]);
}

function citaValidationAdmin(): User
{
    return User::factory()->create(['role' => User::ROLE_ADMIN]);
}

test('store rejects cita when hora_fin equals hora', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 11, 9, 0, 0));

    $paciente = citaValidationPaciente();

    $this->actingAs(citaValidationAdmin())
        ->post(route('citas.store', absolute: false), [
            'paciente_id' => $paciente->id,
            'fecha' => now()->copy()->addDay()->toDateString(),
            'hora' => '10:00',
            'hora_fin' => '10:00',
            'motivo' => 'Revision general',
        ])
        ->assertSessionHasErrors('hora_fin');

    expect(Cita::count())->toBe(0);

    Carbon::setTestNow();
});

test('store rejects cita when hora_fin is before hora', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 11, 9, 0, 0));

    $paciente = citaValidationPaciente();

    $this->actingAs(citaValidationAdmin())
        ->post(route('citas.store', absolute: false), [
            'paciente_id' => $paciente->id,
            'fecha' => now()->copy()->addDay()->toDateString(),
            'hora' => '11:00',
            'hora_fin' => '10:30',
            'motivo' => 'Revision general',
        ])
        ->assertSessionHasErrors([
            'hora_fin' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

    expect(Cita::count())->toBe(0);

    Carbon::setTestNow();
});

test('store accepts cita when hora_fin is after hora', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 11, 9, 0, 0));

    $paciente = citaValidationPaciente();

    $this->actingAs(citaValidationAdmin())
        ->post(route('citas.store', absolute: false), [
            'paciente_id' => $paciente->id,
            'fecha' => now()->copy()->addDay()->toDateString(),
            'hora' => '10:00',
            'hora_fin' => '10:30',
            'motivo' => 'Revision general',
        ])
        ->assertRedirect(route('citas.index', absolute: false))
        ->assertSessionHasNoErrors();

    expect(Cita::count())->toBe(1);
    expect((string) Cita::first()->hora_fin)->toStartWith('10:30');

    Carbon::setTestNow();
});

test('update rejects cita when hora_fin is not after hora', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 11, 9, 0, 0));

    $paciente = citaValidationPaciente();
    $cita = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->copy()->addDay()->toDateString(),
        'hora' => '09:00',
        'hora_fin' => '10:00',
        'motivo' => 'Cita inicial',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    $this->actingAs(citaValidationAdmin())
        ->put(route('citas.update', $cita, absolute: false), [
            'paciente_id' => $paciente->id,
            'fecha' => now()->copy()->addDay()->toDateString(),
            'hora' => '11:00',
            'hora_fin' => '10:30',
            'motivo' => 'Cita actualizada',
            'estado' => Cita::ESTADO_PENDIENTE,
        ])
        ->assertSessionHasErrors('hora_fin');

    expect((string) $cita->refresh()->hora_fin)->toStartWith('10:00');

    Carbon::setTestNow();
});
