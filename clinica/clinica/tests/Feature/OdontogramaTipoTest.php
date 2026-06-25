<?php

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\PiezaDental;
use App\Models\User;
use App\Services\ConsultaService;

function odontogramaPaciente(array $overrides = []): Paciente
{
    static $sequence = 0;
    $sequence++;

    return Paciente::create(array_merge([
        'nombre_completo' => "Paciente Odontograma Tipo {$sequence}",
        'dpi' => (string) (5000000000000 + $sequence),
        'fecha_nacimiento' => '2018-06-24',
        'telefono' => '50255559000',
        'correo' => "odontograma-tipo-{$sequence}@example.com",
        'direccion' => 'Zona 10',
    ], $overrides));
}

test('odontograma endpoint filters temporal pieces and keeps permanent universal mapping', function () {
    $admin = User::factory()->create();
    $paciente = odontogramaPaciente(['fecha_nacimiento' => '1990-01-01']);
    $consulta = Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'fecha' => '2026-06-24',
        'motivo' => 'Revision',
        'diagnostico' => 'Control.',
    ]);

    $permanente = PiezaDental::create([
        'numero' => 18,
        'nombre' => 'Tercer molar',
        'cuadrante' => 1,
        'posicion' => 8,
        'tipo' => PiezaDental::TIPO_PERMANENTE,
    ]);
    $temporal = PiezaDental::firstOrCreate(['numero' => 55], [
        'numero' => 55,
        'nombre' => 'Segundo molar temporal',
        'cuadrante' => 1,
        'posicion' => 5,
        'tipo' => PiezaDental::TIPO_TEMPORAL,
    ]);

    $consulta->piezasDentales()->attach($permanente->id, ['estado' => 'corona']);
    $consulta->piezasDentales()->attach($temporal->id, ['estado' => 'caries']);

    $this->actingAs($admin)
        ->getJson(route('consultas.odontograma.index', $consulta, false).'?tipo=permanente')
        ->assertOk()
        ->assertJsonPath('tipo', PiezaDental::TIPO_PERMANENTE)
        ->assertJsonPath('piezas.0.numero', 1)
        ->assertJsonPath('piezas.0.numero_fdi', 18);

    $this->actingAs($admin)
        ->getJson(route('consultas.odontograma.index', $consulta, false).'?tipo=temporal')
        ->assertOk()
        ->assertJsonCount(20, 'piezas')
        ->assertJsonFragment([
            'numero' => 55,
            'tipo' => PiezaDental::TIPO_TEMPORAL,
            'estado' => 'caries',
        ]);
});

test('consulta service defaults odontograma to temporal for pediatric patients', function () {
    $admin = User::factory()->create();
    $paciente = odontogramaPaciente(['fecha_nacimiento' => '2019-01-01']);
    $consulta = Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'fecha' => '2026-06-24',
        'motivo' => 'Control pediatrico',
        'diagnostico' => 'Paciente infantil.',
    ]);

    $service = app(ConsultaService::class);

    expect($service->tipoOdontogramaInicial($consulta))->toBe(PiezaDental::TIPO_TEMPORAL);
});
