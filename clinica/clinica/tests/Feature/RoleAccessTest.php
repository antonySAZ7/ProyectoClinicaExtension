<?php

use App\Models\User;

test('admin can access dashboard but paciente can not', function () {
    $admin = User::factory()->create();
    $paciente = User::factory()->paciente()->create();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk();

    $this->actingAs($paciente)
        ->get('/dashboard')
        ->assertForbidden();
});

test('paciente can access portal but admin can not', function () {
    $paciente = User::factory()->paciente()->create();
    $admin = User::factory()->create();

    $this->actingAs($paciente)
        ->get('/portal')
        ->assertOk();

    $this->actingAs($admin)
        ->get('/portal')
        ->assertForbidden();
});
