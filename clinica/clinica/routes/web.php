<?php

use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PacientePortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->homeRoute());
    }

    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/archivos/{archivo}', [ArchivoController::class, 'ver'])->name('archivos.ver');
    Route::get('/archivos/{archivo}/descargar', [ArchivoController::class, 'descargar'])
        ->name('archivos.descargar');
});

Route::middleware(['auth', 'role:admin,doctor'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/pacientes/{paciente}/consultas', [ConsultaController::class, 'index'])
        ->name('pacientes.consultas.index');
    Route::get('/pacientes/{paciente}/consultas/create', [ConsultaController::class, 'create'])
        ->name('pacientes.consultas.create');
    Route::post('/pacientes/{paciente}/consultas', [ConsultaController::class, 'store'])
        ->name('pacientes.consultas.store');
    Route::get('/consultas/{consulta}', [ConsultaController::class, 'show'])
        ->name('consultas.show');

    Route::resource('pacientes', PacienteController::class)->except(['show']);
    Route::get('/citas/calendario', [CitaController::class, 'calendario'])->name('citas.calendario');
    Route::resource('citas', CitaController::class)->except(['show']);
});

Route::middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/portal', [PacientePortalController::class, 'index'])->name('portal');
    Route::get('/portal/historial-clinico', [ConsultaController::class, 'portalIndex'])
        ->name('portal.consultas.index');
    Route::get('/portal/historial-clinico/{consulta}', [ConsultaController::class, 'show'])
        ->name('portal.consultas.show');
    Route::patch('/portal/citas/{cita}/cancelar', [PacientePortalController::class, 'cancel'])
        ->name('portal.citas.cancelar');
});

require __DIR__.'/auth.php';
