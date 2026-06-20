<?php

use App\Http\Controllers\AntecedenteClinicoController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\ConsultaPdfController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstadoCuentaPdfController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OdontogramaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PacientePortalController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PrecioController;
use App\Http\Controllers\PresupuestoItemController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analisis', [DashboardController::class, 'analitica'])->name('analitica.index');

    Route::get('/exportar/pacientes', [ExportController::class, 'pacientes'])
        ->name('exportar.pacientes');
    Route::get('/exportar/consultas', [ExportController::class, 'consultas'])
        ->name('exportar.consultas');
    Route::get('/exportar/estado-cuenta', [ExportController::class, 'estadoCuenta'])
        ->name('exportar.estado-cuenta');
    Route::get('/exportar/excel-historico', [ExportController::class, 'excelHistorico'])
        ->name('exportar.excel-historico');

    Route::get('/pacientes/{paciente}/consultas', [ConsultaController::class, 'index'])
        ->name('pacientes.consultas.index');
    Route::get('/pacientes/{paciente}/consultas/create', [ConsultaController::class, 'create'])
        ->name('pacientes.consultas.create');
    Route::post('/pacientes/{paciente}/consultas', [ConsultaController::class, 'store'])
        ->name('pacientes.consultas.store');
    Route::get('/consultas/{consulta}', [ConsultaController::class, 'show'])
        ->name('consultas.show');
    Route::get('/consultas/{consulta}/edit', [ConsultaController::class, 'edit'])
        ->name('consultas.edit');
    Route::put('/consultas/{consulta}', [ConsultaController::class, 'update'])
        ->name('consultas.update');
    Route::post('/consultas/{consulta}/seguimiento', [ConsultaController::class, 'storeFollowUp'])
        ->name('consultas.seguimiento.store');
    Route::post('/consultas/{consulta}/observaciones', [ConsultaController::class, 'storeObservacion'])
        ->name('consultas.observaciones.store');
    Route::delete('/observaciones/{observacion}', [ConsultaController::class, 'destroyObservacion'])
        ->name('observaciones.destroy');
    Route::post('/consultas/{consulta}/archivos', [ConsultaController::class, 'storeArchivo'])
        ->name('consultas.archivos.store');
    Route::delete('/archivos/{archivo}', [ConsultaController::class, 'destroyArchivo'])
        ->name('archivos.destroy');
    Route::post('/consultas/{consulta}/odontograma', [OdontogramaController::class, 'store'])
        ->name('consultas.odontograma.store');
    Route::put('/consultas/{consulta}/odontograma/{pieza}', [OdontogramaController::class, 'update'])
        ->name('consultas.odontograma.update');
    Route::delete('/consultas/{consulta}/odontograma/{pieza}', [OdontogramaController::class, 'destroy'])
        ->name('consultas.odontograma.destroy');
    Route::get('/consultas/{consulta}/pdf', ConsultaPdfController::class)
        ->name('consultas.pdf');
    Route::get('/consultas/{consulta}/estado-cuenta/pdf', [EstadoCuentaPdfController::class, 'consulta'])
        ->name('consultas.estado-cuenta.pdf');
    Route::get('/consultas/{consulta}/presupuesto/sugerencias', [PresupuestoItemController::class, 'suggest'])
        ->name('consultas.presupuesto.sugerencias');
    Route::post('/consultas/{consulta}/presupuesto/aceptar', [PresupuestoItemController::class, 'accept'])
        ->name('consultas.presupuesto.aceptar');
    Route::post('/consultas/{consulta}/presupuesto', [PresupuestoItemController::class, 'store'])
        ->name('consultas.presupuesto.store');
    Route::put('/consultas/{consulta}/presupuesto/{item}', [PresupuestoItemController::class, 'update'])
        ->name('consultas.presupuesto.update');
    Route::delete('/consultas/{consulta}/presupuesto/{item}', [PresupuestoItemController::class, 'destroy'])
        ->name('consultas.presupuesto.destroy');

    Route::get('/pacientes/{paciente}/antecedentes', [AntecedenteClinicoController::class, 'edit'])
        ->name('pacientes.antecedentes.edit');
    Route::put('/pacientes/{paciente}/antecedentes', [AntecedenteClinicoController::class, 'update'])
        ->name('pacientes.antecedentes.update');
    Route::post('/pacientes/{paciente}/pagos', [PagoController::class, 'store'])
        ->name('pacientes.pagos.store');

    Route::get('/pacientes/{paciente}/odontograma/evolucion', [PacienteController::class, 'evolucionOdontograma'])
        ->name('pacientes.odontograma.evolucion');

    Route::get('/pacientes/{paciente}/estado-cuenta/pdf', [EstadoCuentaPdfController::class, 'paciente'])
        ->name('pacientes.estado-cuenta.pdf');

    Route::resource('pacientes', PacienteController::class);
    Route::get('/citas/calendario', [CitaController::class, 'calendario'])->name('citas.calendario');
    Route::resource('citas', CitaController::class)->except(['show']);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/precios', [PrecioController::class, 'index'])->name('precios.index');
    Route::patch('/precios/servicios/{servicio}', [PrecioController::class, 'updateServicio'])
        ->name('precios.servicios.update');
    Route::post('/precios/tarifas', [PrecioController::class, 'storeTarifa'])
        ->name('precios.tarifas.store');
    Route::patch('/precios/tarifas/{tarifa}', [PrecioController::class, 'updateTarifa'])
        ->name('precios.tarifas.update');
    Route::delete('/precios/tarifas/{tarifa}', [PrecioController::class, 'destroyTarifa'])
        ->name('precios.tarifas.destroy');
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
