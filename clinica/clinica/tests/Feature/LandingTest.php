<?php

use App\Models\User;

test('guest can view landing at /', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('DENS32');
    $response->assertSee('Comprometidos con tu salud');
    $response->assertSee('Acerca de nosotros');
});

test('authenticated paciente still sees landing without redirect', function () {
    $paciente = User::factory()->paciente()->create();

    $response = $this->actingAs($paciente)->get('/');

    $response->assertOk();
    $response->assertSee('DENS32');
    $response->assertSee('Mis citas');
});

test('authenticated admin still sees landing without redirect', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get('/');

    $response->assertOk();
    $response->assertSee('DENS32');
    $response->assertSee('Dashboard');
});

test('landing exposes contact info from config', function () {
    $response = $this->get('/');

    $response->assertSee(config('site.brand.phone_display'));
    $response->assertSee(config('site.brand.instagram_display'));
    $response->assertSee(config('site.brand.email_display'));
});
