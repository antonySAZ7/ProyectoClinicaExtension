<?php

use App\Http\Controllers\Api\DisponibilidadController;
use Illuminate\Support\Facades\Route;

Route::get('/disponibilidad', DisponibilidadController::class)
    ->name('api.disponibilidad');
