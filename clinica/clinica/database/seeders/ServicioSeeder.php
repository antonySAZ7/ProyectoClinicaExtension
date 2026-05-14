<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Limpieza', 'duracion_minutos' => 30],
            ['nombre' => 'Consulta general', 'duracion_minutos' => 45],
            ['nombre' => 'Ortodoncia', 'duracion_minutos' => 60],
            ['nombre' => 'Endodoncia', 'duracion_minutos' => 90],
            ['nombre' => 'Extraccion', 'duracion_minutos' => 60],
        ];

        foreach ($servicios as $servicio) {
            Servicio::updateOrCreate(
                ['nombre' => $servicio['nombre']],
                [
                    'descripcion' => $servicio['descripcion'] ?? null,
                    'duracion_minutos' => $servicio['duracion_minutos'],
                    'activo' => true,
                ]
            );
        }
    }
}
