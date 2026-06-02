<?php

use App\Mail\ConsultaCerradaMail;
use App\Models\AntecedenteClinico;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Models\NotificacionLog;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\PiezaDental;
use App\Models\TarifaTratamiento;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

function sprint9Paciente(array $overrides = []): Paciente
{
    static $sequence = 0;
    $sequence++;

    return Paciente::create(array_merge([
        'nombre_completo' => "Paciente Sprint 9 {$sequence}",
        'dpi' => (string) (3000000000000 + $sequence),
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255550000',
        'correo' => "sprint9-{$sequence}@example.com",
        'direccion' => 'Zona 1',
    ], $overrides));
}

function sprint9Consulta(Paciente $paciente, User $user, array $overrides = []): Consulta
{
    return Consulta::create(array_merge([
        'paciente_id' => $paciente->id,
        'user_id' => $user->id,
        'fecha' => '2026-06-02',
        'motivo' => 'Consulta sprint 9',
        'diagnostico' => 'Diagnostico inicial.',
    ], $overrides));
}

test('budget items and partial payments update patient balance', function () {
    $admin = User::factory()->create();
    $paciente = sprint9Paciente();
    $consulta = sprint9Consulta($paciente, $admin);

    $this->actingAs($admin)
        ->postJson(route('consultas.presupuesto.store', $consulta, false), [
            'diagnostico' => 'Caries',
            'tratamiento' => 'Restauracion',
            'precio_unitario' => 250,
            'cantidad' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('presupuesto_total', 500);

    $paciente->load(['consultas.presupuestoItems', 'pagos']);

    expect((float) $paciente->presupuesto_total)->toBe(500.0)
        ->and((float) $paciente->total_pagado)->toBe(0.0)
        ->and((float) $paciente->saldo_pendiente)->toBe(500.0);

    $this->actingAs($admin)
        ->postJson(route('pacientes.pagos.store', $paciente, false), [
            'consulta_id' => $consulta->id,
            'monto' => 200,
            'metodo_pago' => 'efectivo',
        ])
        ->assertCreated()
        ->assertJsonPath('saldo_pendiente', 300)
        ->assertJsonPath('total_pagado', 200);

    $this->actingAs($admin)
        ->postJson(route('pacientes.pagos.store', $paciente, false), [
            'consulta_id' => $consulta->id,
            'monto' => 301,
            'metodo_pago' => 'tarjeta',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('monto');
});

test('accepted budgets can not be edited', function () {
    $admin = User::factory()->create();
    $paciente = sprint9Paciente();
    $consulta = sprint9Consulta($paciente, $admin);
    $item = ConsultaPresupuestoItem::create([
        'consulta_id' => $consulta->id,
        'diagnostico' => 'Caries',
        'tratamiento' => 'Restauracion',
        'precio_unitario' => 250,
        'cantidad' => 1,
    ]);

    $this->actingAs($admin)
        ->postJson(route('consultas.presupuesto.aceptar', $consulta, false))
        ->assertOk()
        ->assertJsonPath('consulta_id', $consulta->id);

    $this->actingAs($admin)
        ->postJson(route('consultas.presupuesto.store', $consulta, false), [
            'diagnostico' => 'Nueva caries',
            'tratamiento' => 'Nueva restauracion',
            'precio_unitario' => 100,
            'cantidad' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('presupuesto');

    $this->actingAs($admin)
        ->putJson(route('consultas.presupuesto.update', [$consulta, $item], false), [
            'diagnostico' => 'Caries editada',
            'tratamiento' => 'Restauracion',
            'precio_unitario' => 150,
            'cantidad' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('presupuesto');

    $this->actingAs($admin)
        ->deleteJson(route('consultas.presupuesto.destroy', [$consulta, $item], false))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('presupuesto');

    expect($item->fresh())->not->toBeNull();
});

test('odontogram findings generate budget suggestions from tariff catalog', function () {
    $admin = User::factory()->create();
    $paciente = sprint9Paciente();
    $consulta = sprint9Consulta($paciente, $admin);
    $pieza = PiezaDental::create([
        'numero' => 11,
        'nombre' => 'Incisivo central',
        'cuadrante' => 1,
        'posicion' => 1,
    ]);

    TarifaTratamiento::create([
        'estado_pieza' => 'caries',
        'nombre_legible' => 'Restauracion por caries',
        'precio_sugerido' => 275,
        'activo' => true,
    ]);

    $consulta->piezasDentales()->attach($pieza->id, [
        'estado' => 'caries',
        'observaciones' => 'Lesion en borde incisal.',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('consultas.presupuesto.sugerencias', $consulta, false))
        ->assertOk()
        ->assertJsonPath('items.0.pieza_numero', 11)
        ->assertJsonPath('items.0.estado_pieza', 'caries')
        ->assertJsonPath('items.0.tratamiento', 'Restauracion por caries');

    expect((float) $response->json('items.0.precio_unitario'))->toBe(275.0);
});

test('follow up consultations copy odontogram state from original consultation', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 2, 9, 0, 0));

    $admin = User::factory()->create();
    $paciente = sprint9Paciente();
    $consulta = sprint9Consulta($paciente, $admin, ['fecha' => '2026-05-20']);
    $pieza = PiezaDental::create([
        'numero' => 12,
        'nombre' => 'Incisivo lateral',
        'cuadrante' => 1,
        'posicion' => 2,
    ]);

    $consulta->piezasDentales()->attach($pieza->id, [
        'estado' => 'corona',
        'observaciones' => 'Control de corona provisional.',
    ]);

    $this->actingAs($admin)
        ->post(route('consultas.seguimiento.store', $consulta, false))
        ->assertRedirect();

    $seguimiento = Consulta::where('consulta_origen_id', $consulta->id)->firstOrFail();
    $seguimiento->load('piezasDentales');
    $piezaSeguimiento = $seguimiento->piezasDentales->first();

    expect($seguimiento->paciente_id)->toBe($paciente->id)
        ->and($seguimiento->fecha->toDateString())->toBe('2026-06-02')
        ->and($piezaSeguimiento->pivot->estado)->toBe('corona')
        ->and($piezaSeguimiento->pivot->observaciones)->toBe('Control de corona provisional.');

    Carbon::setTestNow();
});

test('expired appointments without consultation are closed as no show', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 2, 9, 0, 0));

    $admin = User::factory()->create();
    $paciente = sprint9Paciente();

    $pastPending = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => '2026-06-01',
        'hora' => '09:00',
        'motivo' => 'Pendiente vencida',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);
    $pastConfirmed = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => '2026-06-01',
        'hora' => '10:00',
        'motivo' => 'Confirmada vencida',
        'estado' => Cita::ESTADO_CONFIRMADA,
    ]);
    $pastCancelled = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => '2026-06-01',
        'hora' => '11:00',
        'motivo' => 'Cancelada vencida',
        'estado' => Cita::ESTADO_CANCELADA,
    ]);
    $pastWithConsultation = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => '2026-06-01',
        'hora' => '12:00',
        'motivo' => 'Atendida vencida',
        'estado' => Cita::ESTADO_CONFIRMADA,
    ]);
    Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => '2026-06-03',
        'hora' => '09:00',
        'motivo' => 'Pendiente futura',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);
    sprint9Consulta($paciente, $admin, ['cita_id' => $pastWithConsultation->id]);

    $this->artisan('clinica:cerrar-citas-vencidas')
        ->expectsOutput('Citas vencidas cerradas como no_show: 2.')
        ->assertSuccessful();

    expect($pastPending->fresh()->estado)->toBe(Cita::ESTADO_NO_SHOW)
        ->and($pastConfirmed->fresh()->estado)->toBe(Cita::ESTADO_NO_SHOW)
        ->and($pastCancelled->fresh()->estado)->toBe(Cita::ESTADO_CANCELADA)
        ->and($pastWithConsultation->fresh()->estado)->toBe(Cita::ESTADO_CONFIRMADA);

    Carbon::setTestNow();
});

test('closing a consultation sends patient email and notification log', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $paciente = sprint9Paciente(['correo' => 'consulta-cerrada@example.com']);

    $this->actingAs($admin)
        ->post(route('pacientes.consultas.store', $paciente, false), [
            'fecha' => '2026-06-02',
            'motivo' => 'Control general',
            'diagnostico' => 'Consulta cerrada para notificacion.',
            'observaciones' => 'Paciente estable.',
        ])
        ->assertRedirect();

    Mail::assertSent(ConsultaCerradaMail::class, function (ConsultaCerradaMail $mail) use ($paciente) {
        return $mail->consulta->paciente_id === $paciente->id;
    });

    expect(NotificacionLog::where('tipo', 'consulta_cerrada')
        ->where('destinatario', 'consulta-cerrada@example.com')
        ->exists())->toBeTrue();
});

test('clinical and financial models write audit records', function () {
    config(['audit.console' => true]);

    $admin = User::factory()->create();
    $paciente = sprint9Paciente();
    $consulta = sprint9Consulta($paciente, $admin);

    $this->actingAs($admin);

    AntecedenteClinico::create([
        'paciente_id' => $paciente->id,
        'presento_complicacion' => false,
        'en_tratamiento_medico' => false,
        'toma_medicamento' => false,
        'alergico_medicamento' => false,
    ]);

    ConsultaPresupuestoItem::create([
        'consulta_id' => $consulta->id,
        'diagnostico' => 'Caries',
        'tratamiento' => 'Restauracion',
        'precio_unitario' => 250,
        'cantidad' => 1,
    ]);

    Pago::create([
        'paciente_id' => $paciente->id,
        'consulta_id' => $consulta->id,
        'monto' => 100,
        'metodo_pago' => 'efectivo',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => '2026-06-02',
    ]);

    expect(DB::table('audits')->where('auditable_type', AntecedenteClinico::class)->exists())->toBeTrue()
        ->and(DB::table('audits')->where('auditable_type', Consulta::class)->exists())->toBeTrue()
        ->and(DB::table('audits')->where('auditable_type', ConsultaPresupuestoItem::class)->exists())->toBeTrue()
        ->and(DB::table('audits')->where('auditable_type', Pago::class)->exists())->toBeTrue();
});
