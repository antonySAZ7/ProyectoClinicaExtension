<?php

use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ConsultaPdfController;
use App\Http\Controllers\OdontogramaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PacientePortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCitaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/nosotros', [LandingController::class, 'nosotros'])->name('landing.nosotros');
Route::get('/objetivos', [LandingController::class, 'objetivos'])->name('landing.objetivos');
Route::get('/contacto', [LandingController::class, 'contacto'])->name('landing.contacto');

Route::get('/agendar-cita', [PublicCitaController::class, 'create'])->name('public.citas.create');
Route::get('/agendar-cita/disponibilidad', [PublicCitaController::class, 'availability'])
    ->name('public.citas.disponibilidad');
Route::post('/agendar-cita', [PublicCitaController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('public.citas.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/archivos/{archivo}', [ArchivoController::class, 'ver'])->name('archivos.ver');
    Route::get('/archivos/{archivo}/descargar', [ArchivoController::class, 'descargar'])
        ->name('archivos.descargar');

    Route::get('/consultas/{consulta}/odontograma', [OdontogramaController::class, 'index'])
        ->name('consultas.odontograma.index');
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
    Route::post('/consultas/{consulta}/odontograma', [OdontogramaController::class, 'store'])
        ->name('consultas.odontograma.store');
    Route::put('/consultas/{consulta}/odontograma/{pieza}', [OdontogramaController::class, 'update'])
        ->name('consultas.odontograma.update');
    Route::delete('/consultas/{consulta}/odontograma/{pieza}', [OdontogramaController::class, 'destroy'])
        ->name('consultas.odontograma.destroy');
    Route::get('/consultas/{consulta}/pdf', ConsultaPdfController::class)
        ->name('consultas.pdf');

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
    Route::post('/citas/{cita}/confirmar', [CitaController::class, 'confirmar'])
        ->name('citas.confirmar');
    Route::patch('/portal/citas/{cita}/cancelar', [PacientePortalController::class, 'cancel'])
        ->name('portal.citas.cancelar');
    Route::patch('/portal/citas/{cita}/reagendar', [PacientePortalController::class, 'reschedule'])
        ->name('portal.citas.reagendar');
});

require __DIR__.'/auth.php';
