<?php

use App\Models\Paciente;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'nombre_completo' => 'Juan Perez',
        'correo' => 'juan.perez@example.com',
        'dpi' => '1234567890123',
        'fecha_nacimiento' => '1990-05-12',
        'telefono' => '55512345',
        'direccion' => 'Calle Falsa 123, Zona 1',
        'sexo' => 'masculino',
        'estado_civil' => 'soltero',
        'ocupacion' => 'ingeniero',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('portal', absolute: false));

    $user = auth()->user();
    expect($user->role)->toBe(User::ROLE_PACIENTE);
    expect($user->name)->toBe('Juan Perez');
    expect($user->email)->toBe('juan.perez@example.com');

    $paciente = Paciente::where('user_id', $user->id)->first();
    expect($paciente)->not->toBeNull();
    expect($paciente->dpi)->toBe('1234567890123');
    expect($paciente->correo)->toBe('juan.perez@example.com');
    expect($paciente->direccion)->toBe('Calle Falsa 123, Zona 1');
    expect($paciente->sexo)->toBe('masculino');
});

test('registration rejects duplicate dpi', function () {
    Paciente::create([
        'nombre_completo' => 'Existente',
        'dpi' => '9999999999999',
        'fecha_nacimiento' => '1985-01-01',
        'telefono' => '55599999',
        'correo' => 'existente@example.com',
        'direccion' => 'Direccion existente',
    ]);

    $response = $this->post('/register', [
        'nombre_completo' => 'Otro Paciente',
        'correo' => 'otro@example.com',
        'dpi' => '9999999999999',
        'fecha_nacimiento' => '1990-05-12',
        'telefono' => '55512345',
        'direccion' => 'Calle Falsa 123',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('dpi');
    $this->assertGuest();
    expect(User::where('email', 'otro@example.com')->exists())->toBeFalse();
});
