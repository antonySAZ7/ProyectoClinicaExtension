<?php

use App\Http\Controllers\CitaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PacientePortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('pacientes', PacienteController::class)->except(['show']);
    Route::get('/citas/calendario', [CitaController::class, 'calendario'])->name('citas.calendario');
    Route::resource('citas', CitaController::class)->except(['show']);
});

Route::middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/portal', [PacientePortalController::class, 'index'])->name('portal');
    Route::patch('/portal/citas/{cita}/cancelar', [PacientePortalController::class, 'cancel'])
        ->name('portal.citas.cancelar');
});

require __DIR__ . '/auth.php';
