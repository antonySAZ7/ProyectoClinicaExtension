<?php

namespace App\Observers;

use App\Http\Controllers\DashboardController;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer compartido que invalida el cache del dashboard cuando un Cita o
 * Paciente se crea, actualiza o elimina. Aplicado tanto a App\Models\Cita
 * como a App\Models\Paciente desde AppServiceProvider.
 */
class DashboardCacheObserver
{
    public function created(Model $model): void
    {
        DashboardController::invalidate();
    }

    public function updated(Model $model): void
    {
        DashboardController::invalidate();
    }

    public function deleted(Model $model): void
    {
        DashboardController::invalidate();
    }
}
