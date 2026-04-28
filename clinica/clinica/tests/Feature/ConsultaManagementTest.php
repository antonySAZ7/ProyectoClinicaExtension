<?php

use App\Models\Archivo;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin can register a consulta with observations and archivos', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $paciente = Paciente::create([
        'nombre_completo' => 'Paciente Historial',
        'dpi' => '1000000000100',
        'fecha_nacimiento' => '1995-05-10',
        'telefono' => '5555-0100',
        'correo' => 'paciente-historial@example.com',
        'direccion' => 'Zona 10',
    ]);

    $response = $this->actingAs($admin)->post(route('pacientes.consultas.store', $paciente), [
        'fecha' => '2026-04-21',
        'motivo' => 'Consulta general',
        'diagnostico' => 'Paciente estable con seguimiento recomendado.',
        'observaciones' => "Se recomendaron examenes de control.\nSe programo una nueva revision para el proximo mes.",
        'archivos' => [
            UploadedFile::fake()->create('radiografia.jpg', 120, 'image/jpeg'),
            UploadedFile::fake()->create('resultado.pdf', 120, 'application/pdf'),
        ],
    ]);

    $consulta = Consulta::query()->with('archivos')->firstOrFail();

    $response->assertRedirect(route('consultas.show', $consulta));

    $this->assertDatabaseHas('consultas', [
        'id' => $consulta->id,
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'motivo' => 'Consulta general',
    ]);
    $this->assertDatabaseCount('observaciones', 1);
    $this->assertDatabaseCount('archivos', 2);

    foreach ($consulta->archivos as $archivo) {
        Storage::disk('public')->assertExists($archivo->ruta);
    }
});

test('paciente can only view their own consultas from the portal', function () {
    $admin = User::factory()->create();
    $userPaciente = User::factory()->paciente()->create();
    $userAjeno = User::factory()->paciente()->create();

    $paciente = Paciente::create([
        'user_id' => $userPaciente->id,
        'nombre_completo' => 'Paciente Propio',
        'dpi' => '1000000000101',
        'fecha_nacimiento' => '1994-04-11',
        'telefono' => '5555-0101',
        'correo' => 'paciente-propio@example.com',
        'direccion' => 'Zona 11',
    ]);

    $pacienteAjeno = Paciente::create([
        'user_id' => $userAjeno->id,
        'nombre_completo' => 'Paciente Ajeno',
        'dpi' => '1000000000102',
        'fecha_nacimiento' => '1993-03-12',
        'telefono' => '5555-0102',
        'correo' => 'paciente-ajeno@example.com',
        'direccion' => 'Zona 12',
    ]);

    $consultaPropia = Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'fecha' => '2026-04-20',
        'motivo' => 'Control propio',
        'diagnostico' => 'Todo evoluciona correctamente.',
    ]);

    $consultaAjena = Consulta::create([
        'paciente_id' => $pacienteAjeno->id,
        'user_id' => $admin->id,
        'fecha' => '2026-04-19',
        'motivo' => 'Consulta ajena',
        'diagnostico' => 'No debe ser visible para otro paciente.',
    ]);

    $this->actingAs($userPaciente)
        ->get(route('portal.consultas.index'))
        ->assertOk()
        ->assertSee('Control propio')
        ->assertDontSee('Consulta ajena');

    $this->actingAs($userPaciente)
        ->get(route('portal.consultas.show', $consultaPropia))
        ->assertOk()
        ->assertSee('Todo evoluciona correctamente.');

    $this->actingAs($userPaciente)
        ->get(route('portal.consultas.show', $consultaAjena))
        ->assertForbidden();
});

test('archivo access is gated by role and ownership', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $userPaciente = User::factory()->paciente()->create();
    $userAjeno = User::factory()->paciente()->create();

    $paciente = Paciente::create([
        'user_id' => $userPaciente->id,
        'nombre_completo' => 'Paciente Archivos',
        'dpi' => '1000000000200',
        'fecha_nacimiento' => '1990-01-01',
        'telefono' => '5555-0200',
        'correo' => 'paciente-archivos@example.com',
        'direccion' => 'Zona 1',
    ]);

    $consulta = Consulta::create([
        'paciente_id' => $paciente->id,
        'user_id' => $admin->id,
        'fecha' => '2026-04-26',
        'motivo' => 'Adjuntos',
        'diagnostico' => 'Pruebas de archivo.',
    ]);

    $ruta = UploadedFile::fake()->create('estudio.pdf', 50, 'application/pdf')
        ->store("consultas/{$paciente->id}", 'public');

    $archivo = Archivo::create([
        'consulta_id' => $consulta->id,
        'ruta' => $ruta,
        'tipo' => 'application/pdf',
        'nombre_original' => 'estudio.pdf',
    ]);

    // No autenticado: redirige a login
    $this->get(route('archivos.ver', $archivo))->assertRedirect(route('login'));

    // Admin puede ver y descargar
    $this->actingAs($admin)->get(route('archivos.ver', $archivo))->assertOk();
    $this->actingAs($admin)->get(route('archivos.descargar', $archivo))->assertOk();

    // Paciente dueño puede ver y descargar el suyo
    $this->actingAs($userPaciente)->get(route('archivos.ver', $archivo))->assertOk();
    $this->actingAs($userPaciente)->get(route('archivos.descargar', $archivo))->assertOk();

    // Paciente ajeno recibe 403
    $this->actingAs($userAjeno)->get(route('archivos.ver', $archivo))->assertForbidden();
    $this->actingAs($userAjeno)->get(route('archivos.descargar', $archivo))->assertForbidden();
});

test('doctor can access consulta creation routes and paciente can not', function () {
    $doctor = User::factory()->doctor()->create();
    $userPaciente = User::factory()->paciente()->create();

    $paciente = Paciente::create([
        'user_id' => $userPaciente->id,
        'nombre_completo' => 'Paciente Ruta',
        'dpi' => '1000000000103',
        'fecha_nacimiento' => '1992-02-13',
        'telefono' => '5555-0103',
        'correo' => 'paciente-ruta@example.com',
        'direccion' => 'Zona 13',
    ]);

    $this->actingAs($doctor)
        ->get(route('pacientes.consultas.create', $paciente))
        ->assertOk();

    $this->actingAs($userPaciente)
        ->get(route('pacientes.consultas.create', $paciente))
        ->assertForbidden();
});
