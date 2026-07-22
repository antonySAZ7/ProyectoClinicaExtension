<?php

use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;
use App\Services\ExcelHistoricoService;

/**
 * Cobertura del export en formato DENS 32 (ExcelHistoricoService + ruta).
 *
 * Verifica lo que da valor y lo que puede regresionar:
 *  - Que se generen las dos hojas espejo del Excel original (BD y Estatus).
 *  - Que el "Presupuesto Total" se recalcule desde las líneas reales
 *    (el Excel original lo capturaba a mano y su fórmula estaba rota).
 *  - Que los abonos y el saldo salgan de los pagos cobrados.
 *  - Que los ceros/celdas vacías NO se omitan (regresión del bug de
 *    strictNullComparison de PhpSpreadsheet ya corregido en el service).
 *  - Que la ruta descargue un .xlsx válido y esté protegida.
 */
function exportPaciente(array $overrides = []): Paciente
{
    static $sequence = 0;
    $sequence++;

    return Paciente::create(array_merge([
        'nombre_completo' => "Paciente Export {$sequence}",
        'dpi' => (string) (4100000000000 + $sequence),
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255551000',
        'correo' => "export-{$sequence}@example.com",
        'direccion' => 'Zona 1',
    ], $overrides));
}

function exportConsulta(Paciente $paciente, User $user): Consulta
{
    return Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $user->id,
        'fecha' => '2026-06-02',
        'motivo' => 'Control',
        'diagnostico' => 'Sin novedad.',
    ]);
}

test('el export genera las hojas BD y Estatus', function () {
    $admin = User::factory()->create();
    exportConsulta(exportPaciente(), $admin);

    $libro = app(ExcelHistoricoService::class)->generar();

    expect($libro->getSheetCount())->toBe(2)
        ->and($libro->getSheet(0)->getTitle())->toBe('BD')
        ->and($libro->getSheet(1)->getTitle())->toBe('Estatus');
});

test('el presupuesto total se recalcula desde las lineas y los abonos salen de los pagos cobrados', function () {
    $admin = User::factory()->create();
    $paciente = exportPaciente();
    $consulta = exportConsulta($paciente, $admin);

    ConsultaPresupuestoItem::create([
        'consulta_id' => $consulta->id,
        'diagnostico' => 'Caries',
        'tratamiento' => 'Restauracion',
        'precio_unitario' => 250,
        'cantidad' => 1,
    ]); // 250

    ConsultaPresupuestoItem::create([
        'consulta_id' => $consulta->id,
        'diagnostico' => 'Corona',
        'tratamiento' => 'Corona',
        'precio_unitario' => 300,
        'cantidad' => 2,
    ]); // 600  -> total 850

    Pago::create([
        'paciente_id' => $paciente->id,
        'consulta_id' => $consulta->id,
        'monto' => 400,
        'metodo_pago' => 'efectivo',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => '2026-06-03',
    ]);

    $libro = app(ExcelHistoricoService::class)->generar();

    // Hoja BD: la última columna es "Presupuesto Total".
    $bd = $libro->getSheet(0);
    $colTotal = $bd->getHighestColumn();
    expect((float) $bd->getCell($colTotal.'3')->getValue())->toBe(850.0);

    // Hoja Estatus: Presupuesto (I), Abonos (J), Nuevo Saldo (K).
    $estatus = $libro->getSheet(1);
    expect((float) $estatus->getCell('I3')->getValue())->toBe(850.0)
        ->and((float) $estatus->getCell('J3')->getValue())->toBe(400.0)
        ->and((float) $estatus->getCell('K3')->getValue())->toBe(450.0);
});

test('una consulta sin lineas exporta el total en cero, no en blanco', function () {
    $admin = User::factory()->create();
    exportConsulta(exportPaciente(), $admin);

    $bd = app(ExcelHistoricoService::class)->generar()->getSheet(0);
    $colTotal = $bd->getHighestColumn();
    $valor = $bd->getCell($colTotal.'3')->getValue();

    expect($valor)->not->toBeNull()
        ->and((float) $valor)->toBe(0.0);
});

test('la ruta de export descarga un xlsx valido para un admin', function () {
    $admin = User::factory()->create();
    exportConsulta(exportPaciente(), $admin);

    $response = $this->actingAs($admin)
        ->get(route('exportar.excel-historico', [], false));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // Un .xlsx es un archivo ZIP: su contenido empieza con la firma "PK".
    expect(str_starts_with($response->streamedContent(), 'PK'))->toBeTrue();
});

test('la ruta de export esta protegida para invitados', function () {
    $this->get(route('exportar.excel-historico', [], false))
        ->assertRedirect(route('login', [], false));
});
