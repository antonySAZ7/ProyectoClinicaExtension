<?php

use App\Mail\AdminAppointmentCreatedMail;
use App\Mail\AdminUserRegisteredMail;
use App\Mail\CitaConfirmationMail;
use App\Mail\SeguimientoReminderMail;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\HorarioClinica;
use App\Models\NotificacionLog;
use App\Models\Paciente;
use App\Models\PiezaDental;
use App\Models\RecordatorioSeguimiento;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

test('availability endpoint returns free blocks excluding overlapping appointments', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 11, 10, 0, 0));

    $servicio = Servicio::create([
        'nombre' => 'Limpieza',
        'duracion_minutos' => 30,
        'activo' => true,
    ]);

    HorarioClinica::create([
        'dia_semana' => 2,
        'hora_apertura' => '08:00',
        'hora_cierre' => '10:00',
        'activo' => true,
    ]);

    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Disponibilidad',
        'dpi' => '2000000000001',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255550001',
        'correo' => 'disponibilidad@example.com',
        'direccion' => 'Zona 1',
    ]);

    Cita::create([
        'paciente_id' => $paciente->id,
        'servicio_id' => $servicio->id,
        'fecha' => '2026-05-12',
        'hora' => '08:30',
        'hora_fin' => '09:00',
        'motivo' => 'Cita existente',
        'estado' => Cita::ESTADO_PENDIENTE,
    ]);

    $response = $this->getJson("/api/disponibilidad?fecha=2026-05-12&servicio_id={$servicio->id}");

    $response->assertOk();
    $horas = collect($response->json('bloques'))->pluck('hora');

    expect($horas)->toContain('08:00')
        ->and($horas)->not->toContain('08:15')
        ->and($horas)->not->toContain('08:30')
        ->and($horas)->toContain('09:00');

    Carbon::setTestNow();
});

test('public appointment scheduling creates paciente user cita and notifications', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 11, 10, 0, 0));
    Mail::fake();

    $admin = User::factory()->create([
        'email' => 'admin-citas@example.com',
    ]);

    $servicio = Servicio::create([
        'nombre' => 'Consulta general',
        'duracion_minutos' => 45,
        'activo' => true,
    ]);

    HorarioClinica::create([
        'dia_semana' => 2,
        'hora_apertura' => '08:00',
        'hora_cierre' => '17:00',
        'activo' => true,
    ]);

    $this->post(route('public.citas.store', absolute: false), [
        'nombre_completo' => 'Paciente Publico',
        'correo' => 'publico@example.com',
        'dpi' => '2000000000002',
        'fecha_nacimiento' => '1994-01-01',
        'telefono' => '50255550002',
        'direccion' => 'Zona 2',
        'password' => 'password',
        'password_confirmation' => 'password',
        'servicio_id' => $servicio->id,
        'fecha' => '2026-05-12',
        'hora' => '09:00',
    ])->assertRedirect(route('portal', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => 'publico@example.com',
        'role' => User::ROLE_PACIENTE,
    ]);
    $this->assertDatabaseHas('pacientes', ['correo' => 'publico@example.com']);
    $this->assertDatabaseHas('citas', [
        'servicio_id' => $servicio->id,
        'hora' => '09:00',
        'hora_fin' => '09:45',
    ]);

    Mail::assertSent(CitaConfirmationMail::class);
    Mail::assertSent(AdminAppointmentCreatedMail::class, function (AdminAppointmentCreatedMail $mail) use ($admin) {
        return $mail->hasTo($admin->email)
            && $mail->cita->paciente?->correo === 'publico@example.com';
    });
    Mail::assertSent(AdminUserRegisteredMail::class, function (AdminUserRegisteredMail $mail) use ($admin) {
        return $mail->hasTo($admin->email)
            && $mail->registeredUser->email === 'publico@example.com';
    });

    expect(NotificacionLog::where('canal', 'email')
        ->where('tipo', 'confirmacion_agendamiento')
        ->exists())->toBeTrue()
        ->and(NotificacionLog::where('canal', 'email')
            ->where('tipo', 'admin_cita_creada')
            ->where('destinatario', $admin->email)
            ->exists())->toBeTrue()
        ->and(NotificacionLog::where('canal', 'email')
            ->where('tipo', 'admin_usuario_registrado')
            ->where('destinatario', $admin->email)
            ->exists())->toBeTrue();

    Carbon::setTestNow();
});

test('backoffice can update consulta odontograma pieces', function () {
    $admin = User::factory()->create();
    $pieza = PiezaDental::create([
        'numero' => 11,
        'nombre' => 'Incisivo central',
        'cuadrante' => 1,
        'posicion' => 1,
    ]);
    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Odontograma',
        'dpi' => '2000000000004',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255550004',
        'correo' => 'odontograma@example.com',
        'direccion' => 'Zona 4',
    ]);
    $consulta = Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'fecha' => '2026-05-12',
        'motivo' => 'Control',
        'diagnostico' => 'Revision odontograma.',
    ]);

    $this->actingAs($admin)
        ->putJson(route('consultas.odontograma.update', [$consulta, $pieza], false), [
            'estado' => 'caries',
            'observaciones' => 'Caries incipiente.',
        ])
        ->assertOk()
        ->assertJsonPath('piezas.0.estado', 'caries');

    $this->assertDatabaseHas('consulta_pieza_dental', [
        'consulta_id' => $consulta->id,
        'pieza_id' => $pieza->id,
        'estado' => 'caries',
    ]);
});

test('backoffice can export consulta pdf', function () {
    $admin = User::factory()->create();
    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente PDF',
        'dpi' => '2000000000005',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255550005',
        'correo' => 'pdf@example.com',
        'direccion' => 'Zona 5',
    ]);
    $consulta = Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'fecha' => '2026-05-12',
        'motivo' => 'Exportacion',
        'diagnostico' => 'Consulta lista para PDF.',
    ]);

    $this->actingAs($admin)
        ->get(route('consultas.pdf', $consulta, false))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('admin can create appointment with interval follow up reminder', function () {
    Carbon::setTestNow(Carbon::create(2026, 1, 10, 9, 0, 0));

    $admin = User::factory()->create();
    $servicio = Servicio::create([
        'nombre' => 'Limpieza',
        'duracion_minutos' => 30,
        'activo' => true,
    ]);
    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Seguimiento',
        'dpi' => '2000000000006',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255550006',
        'correo' => 'seguimiento@example.com',
        'direccion' => 'Zona 6',
    ]);

    $this->actingAs($admin)
        ->post(route('citas.store', absolute: false), [
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-01-10',
            'hora' => '10:00',
            'hora_fin' => '10:30',
            'motivo' => 'Limpieza dental',
            'estado' => Cita::ESTADO_PENDIENTE,
            'activar_recordatorio_seguimiento' => '1',
            'recordatorio_modo' => RecordatorioSeguimiento::MODO_INTERVALO,
            'recordatorio_titulo' => 'Limpieza semestral',
            'recordatorio_intervalo_meses' => 5,
            'recordatorio_dias_antes' => [7, 1, 0],
        ])
        ->assertRedirect(route('citas.index', absolute: false));

    $this->assertDatabaseHas('recordatorios_seguimiento', [
        'paciente_id' => $paciente->id,
        'modo' => RecordatorioSeguimiento::MODO_INTERVALO,
        'titulo' => 'Limpieza semestral',
        'intervalo_meses' => 5,
        'fecha_objetivo' => '2026-06-10 00:00:00',
    ]);

    Carbon::setTestNow();
});

test('follow up command sends due reminders only once per configured date', function () {
    Carbon::setTestNow(Carbon::create(2026, 7, 9, 8, 0, 0));
    Mail::fake();

    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Aviso',
        'dpi' => '2000000000007',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '50255550007',
        'correo' => 'aviso@example.com',
        'direccion' => 'Zona 7',
    ]);
    $cita = Cita::create([
        'paciente_id' => $paciente->id,
        'fecha' => '2026-01-10',
        'hora' => '10:00',
        'hora_fin' => '10:30',
        'motivo' => 'Limpieza',
        'estado' => Cita::ESTADO_CONFIRMADA,
    ]);
    $recordatorio = RecordatorioSeguimiento::create([
        'cita_id' => $cita->id,
        'paciente_id' => $paciente->id,
        'activo' => true,
        'modo' => RecordatorioSeguimiento::MODO_INTERVALO,
        'titulo' => null,
        'intervalo_meses' => 6,
        'fecha_objetivo' => '2026-07-10',
        'dias_antes' => [7, 1, 0],
        'fechas_enviadas' => [],
    ]);

    $this->artisan('followups:send')
        ->expectsOutput('Recordatorios de seguimiento enviados: 1. Omitidos: 0.')
        ->assertSuccessful();

    Mail::assertSent(SeguimientoReminderMail::class, function (SeguimientoReminderMail $mail) use ($recordatorio) {
        return $mail->recordatorio->is($recordatorio);
    });
    $this->assertDatabaseHas('notificaciones_log', [
        'cita_id' => $cita->id,
        'canal' => 'email',
        'tipo' => 'recordatorio_seguimiento',
        'destinatario' => 'aviso@example.com',
        'estado' => 'enviado',
    ]);
    expect($recordatorio->fresh()->displayTitle())->toBe('Seguimiento dental');

    $this->artisan('followups:send')
        ->expectsOutput('Recordatorios de seguimiento enviados: 0. Omitidos: 0.')
        ->assertSuccessful();

    Mail::assertSent(SeguimientoReminderMail::class, 1);

    Carbon::setTestNow();
});
