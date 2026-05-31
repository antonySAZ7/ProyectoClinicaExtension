<?php

namespace App\Providers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Observers\DashboardCacheObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Invalidar el cache del dashboard cuando cambien citas o pacientes.
        Cita::observe(DashboardCacheObserver::class);
        Paciente::observe(DashboardCacheObserver::class);
    }
}
