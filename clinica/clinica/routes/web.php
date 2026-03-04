<?php
use App\Http\Controllers\PacienteController;
use Illuminate\Support\Facades\Route;

Route::resource('pacientes', PacienteController::class);

Route::get('/', function () {
    return view('welcome');
});
