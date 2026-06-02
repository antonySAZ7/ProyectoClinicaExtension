<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Limpieza', 'duracion_minutos' => 30, 'precio_sugerido' => 250],
            ['nombre' => 'Consulta general', 'duracion_minutos' => 45, 'precio_sugerido' => 150],
            ['nombre' => 'Ortodoncia', 'duracion_minutos' => 60, 'precio_sugerido' => 400],
            ['nombre' => 'Endodoncia', 'duracion_minutos' => 90, 'precio_sugerido' => 900],
            ['nombre' => 'Extraccion', 'duracion_minutos' => 60, 'precio_sugerido' => 350],
        ];

        foreach ($servicios as $servicio) {
            Servicio::updateOrCreate(
                ['nombre' => $servicio['nombre']],
                [
                    'descripcion' => $servicio['descripcion'] ?? null,
                    'duracion_minutos' => $servicio['duracion_minutos'],
                    'precio_sugerido' => $servicio['precio_sugerido'],
                    'activo' => true,
                ]
            );
        }
    }
}
