<?php

use App\Models\AntecedenteClinico;
use App\Models\Paciente;
use App\Models\User;

function fichaPaciente(): Paciente
{
    return Paciente::create([
        'nombre_completo' => 'Paciente Ficha',
        'dpi' => '1000000000901',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0901',
        'correo' => 'ficha@example.com',
        'direccion' => 'Zona 5',
    ]);
}

test('admin can save clinical history and missing checkboxes default to false', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $paciente = fichaPaciente();

    $this->actingAs($admin)
        ->put(route('pacientes.antecedentes.update', $paciente, false), [
            'ultima_visita_dental' => '2025-01-15',
            'ultima_visita_motivo' => 'Limpieza',
            'ant_diabetes' => '1',
            'ant_alergias' => '1',
            'odo_sensibilidad' => '1',
            'toma_medicamento' => '1',
            'cual_medicamento' => 'Metformina',
            // ant_cardiovascular ausente -> debe quedar false
        ])
        ->assertRedirect(route('pacientes.antecedentes.edit', $paciente, false))
        ->assertSessionHas('success');

    $ant = $paciente->refresh()->antecedenteClinico;

    expect($ant)->not->toBeNull();
    expect($ant->ant_diabetes)->toBeTrue();
    expect($ant->ant_alergias)->toBeTrue();
    expect($ant->odo_sensibilidad)->toBeTrue();
    expect($ant->ant_cardiovascular)->toBeFalse();
    expect($ant->toma_medicamento)->toBeTrue();
    expect($ant->cual_medicamento)->toBe('Metformina');
    expect($ant->ultima_visita_motivo)->toBe('Limpieza');
});

test('saving clinical history twice updates the same 1:1 record', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $paciente = fichaPaciente();

    $this->actingAs($admin)->put(route('pacientes.antecedentes.update', $paciente, false), [
        'ant_diabetes' => '1',
    ]);

    $this->actingAs($admin)->put(route('pacientes.antecedentes.update', $paciente, false), [
        'ant_diabetes' => '0',
        'ant_hepatitis' => '1',
    ]);

    expect(AntecedenteClinico::where('paciente_id', $paciente->id)->count())->toBe(1);

    $ant = $paciente->refresh()->antecedenteClinico;
    expect($ant->ant_diabetes)->toBeFalse();
    expect($ant->ant_hepatitis)->toBeTrue();
});

test('paciente cannot access or edit clinical history', function () {
    $user = User::factory()->paciente()->create();
    $paciente = fichaPaciente();

    $this->actingAs($user)
        ->get(route('pacientes.antecedentes.edit', $paciente, false))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('pacientes.antecedentes.update', $paciente, false), ['ant_diabetes' => '1'])
        ->assertForbidden();

    expect(AntecedenteClinico::count())->toBe(0);
});

test('consulta stores vital signs', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $paciente = fichaPaciente();

    $this->actingAs($admin)
        ->post(route('pacientes.consultas.store', $paciente, false), [
            'fecha' => now()->toDateString(),
            'motivo' => 'Control',
            'diagnostico' => 'Sano',
            'peso' => '70.50',
            'altura' => '1.75',
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => '72',
            'frecuencia_respiratoria' => '16',
            'signos_otros' => 'Sin novedad',
        ])
        ->assertRedirect();

    $consulta = $paciente->consultas()->latest('id')->first();

    expect($consulta)->not->toBeNull();
    expect((string) $consulta->peso)->toBe('70.50');
    expect($consulta->presion_arterial)->toBe('120/80');
    expect($consulta->frecuencia_cardiaca)->toBe(72);
});
