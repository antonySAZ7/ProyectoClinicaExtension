<?php

namespace App\Providers;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Models\Paciente;
use App\Models\Pago;
use App\Observers\DashboardCacheObserver;
use App\Policies\ConsultaPolicy;
use App\Policies\PacientePolicy;
use App\Policies\PagoPolicy;
use App\Policies\PresupuestoItemPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Paciente::class, PacientePolicy::class);
        Gate::policy(Consulta::class, ConsultaPolicy::class);
        Gate::policy(Pago::class, PagoPolicy::class);
        Gate::policy(ConsultaPresupuestoItem::class, PresupuestoItemPolicy::class);

        // Invalidar el cache del dashboard cuando cambien citas o pacientes.
        Cita::observe(DashboardCacheObserver::class);
        Paciente::observe(DashboardCacheObserver::class);
    }
}
