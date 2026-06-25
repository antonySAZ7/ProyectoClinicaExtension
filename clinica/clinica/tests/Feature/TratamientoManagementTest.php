<?php

use App\Models\Consulta;
use App\Models\FaseTratamiento;
use App\Models\Paciente;
use App\Models\PiezaDental;
use App\Models\Tratamiento;
use App\Models\User;

function tratamientoPaciente(array $overrides = []): Paciente
{
    static $sequence = 0;
    $sequence++;

    return Paciente::create(array_merge([
        'nombre_completo' => "Paciente Tratamiento {$sequence}",
        'dpi' => (string) (4000000000000 + $sequence),
        'fecha_nacimiento' => '1991-01-01',
        'telefono' => '50255550000',
        'correo' => "tratamiento-{$sequence}@example.com",
        'direccion' => 'Zona 1',
    ], $overrides));
}

function tratamientoConsulta(Paciente $paciente, User $user, array $overrides = []): Consulta
{
    return Consulta::create(array_merge([
        'paciente_id' => $paciente->id,
        'user_id' => $user->id,
        'fecha' => '2026-06-24',
        'motivo' => 'Control de tratamiento',
        'diagnostico' => 'Seguimiento clinico.',
    ], $overrides));
}

test('backoffice can manage multi phase treatments for a patient', function () {
    $admin = User::factory()->create();
    $paciente = tratamientoPaciente();
    $consulta = tratamientoConsulta($paciente, $admin);
    $pieza = PiezaDental::create([
        'numero' => 26,
        'nombre' => 'Primer molar',
        'cuadrante' => 2,
        'posicion' => 6,
    ]);

    $this->actingAs($admin)
        ->post(route('pacientes.tratamientos.store', $paciente, false), [
            'pieza_id' => $pieza->id,
            'nombre' => 'Endodoncia pieza 26',
            'descripcion' => 'Plan de varias sesiones.',
            'fecha_inicio' => '2026-06-24',
        ])
        ->assertRedirect(route('pacientes.show', $paciente, false));

    $tratamiento = Tratamiento::query()->firstOrFail();

    expect($tratamiento->paciente_id)->toBe($paciente->id)
        ->and($tratamiento->pieza_id)->toBe($pieza->id)
        ->and($tratamiento->estado)->toBe(Tratamiento::ESTADO_EN_PROGRESO);

    $this->actingAs($admin)
        ->post(route('tratamientos.fases.store', $tratamiento, false), [
            'consulta_id' => $consulta->id,
            'descripcion' => 'Apertura cameral y medicacion.',
            'fecha' => '2026-06-24',
            'completada' => '1',
        ])
        ->assertRedirect(route('pacientes.show', $paciente, false));

    $fase = FaseTratamiento::query()->firstOrFail();

    expect($fase->tratamiento_id)->toBe($tratamiento->id)
        ->and($fase->consulta_id)->toBe($consulta->id)
        ->and($fase->completada)->toBeTrue()
        ->and($fase->orden)->toBe(1);

    $this->actingAs($admin)
        ->patch(route('tratamientos.finalizar', $tratamiento, false))
        ->assertRedirect(route('pacientes.show', $paciente, false));

    expect($tratamiento->fresh()->estado)->toBe(Tratamiento::ESTADO_FINALIZADO);
});

test('treatment phases reject consultations from another patient', function () {
    $admin = User::factory()->create();
    $paciente = tratamientoPaciente();
    $pacienteAjeno = tratamientoPaciente();
    $consultaAjena = tratamientoConsulta($pacienteAjeno, $admin);

    $tratamiento = Tratamiento::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'nombre' => 'Ortodoncia',
        'estado' => Tratamiento::ESTADO_EN_PROGRESO,
        'fecha_inicio' => '2026-06-24',
    ]);

    $this->actingAs($admin)
        ->post(route('tratamientos.fases.store', $tratamiento, false), [
            'consulta_id' => $consultaAjena->id,
            'descripcion' => 'No debe aceptar consulta ajena.',
            'fecha' => '2026-06-24',
        ])
        ->assertSessionHasErrors('consulta_id');

    expect(FaseTratamiento::count())->toBe(0);
});

test('paciente role can not manage treatments from backoffice routes', function () {
    $userPaciente = User::factory()->paciente()->create();
    $paciente = tratamientoPaciente(['user_id' => $userPaciente->id]);

    $this->actingAs($userPaciente)
        ->post(route('pacientes.tratamientos.store', $paciente, false), [
            'nombre' => 'Tratamiento no autorizado',
            'fecha_inicio' => '2026-06-24',
        ])
        ->assertForbidden();
});
