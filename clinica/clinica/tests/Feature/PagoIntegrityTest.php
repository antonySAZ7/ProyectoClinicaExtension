<?php

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Pago;

test('a cita can have multiple partial payments', function () {
    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Pago',
        'dpi' => '1000000000003',
        'fecha_nacimiento' => '1988-03-03',
        'telefono' => '5555-3333',
        'correo' => 'paciente-pago@example.com',
        'direccion' => 'Zona 3',
    ]);

    $cita = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => now()->addDay()->toDateString(),
        'hora' => '10:00',
        'motivo' => 'Consulta con pago',
        'estado' => 'pendiente',
    ]);

    Pago::create([
        'paciente_id' => $paciente->id,
        'cita_id' => $cita->id,
        'monto' => 150,
        'metodo_pago' => 'efectivo',
        'estado' => 'pagado',
        'fecha_pago' => now()->toDateString(),
    ]);

    Pago::create([
        'paciente_id' => $paciente->id,
        'cita_id' => $cita->id,
        'monto' => 200,
        'metodo_pago' => 'tarjeta',
        'estado' => 'pagado',
        'fecha_pago' => now()->toDateString(),
    ]);

    expect($cita->pagos()->count())->toBe(2);
});
