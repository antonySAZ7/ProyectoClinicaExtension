<?php

namespace App\Console\Commands;

use App\Services\CitaService;
use Illuminate\Console\Command;

class CerrarCitasVencidas extends Command
{
    protected $signature = 'clinica:cerrar-citas-vencidas';

    protected $description = 'Marca como no_show las citas vencidas sin consulta vinculada.';

    public function handle(CitaService $service): int
    {
        $cerradas = $service->closeExpiredNoShows();

        $this->info("Citas vencidas cerradas como no_show: {$cerradas}.");

        return self::SUCCESS;
    }
}
