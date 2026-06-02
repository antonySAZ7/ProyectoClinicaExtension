<?php

namespace App\Console\Commands;

use App\Models\Cita;
use Illuminate\Console\Command;

class CerrarCitasVencidas extends Command
{
    protected $signature = 'clinica:cerrar-citas-vencidas';

    protected $description = 'Marca como no_show las citas vencidas sin consulta vinculada.';

    public function handle(): int
    {
        $cerradas = Cita::query()
            ->whereDate('fecha', '<', today()->toDateString())
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
            ->whereDoesntHave('consulta')
            ->update(['estado' => Cita::ESTADO_NO_SHOW]);

        $this->info("Citas vencidas cerradas como no_show: {$cerradas}.");

        return self::SUCCESS;
    }
}
