<?php

namespace Database\Seeders;

use App\Models\TarifaTratamiento;
use Illuminate\Database\Seeder;

class TarifaTratamientoSeeder extends Seeder
{
    public function run(): void
    {
        $tarifas = [
            ['estado_pieza' => 'caries', 'nombre_legible' => 'Tratamiento de caries', 'precio_sugerido' => 250],
            ['estado_pieza' => 'obturada', 'nombre_legible' => 'Revisión de obturación', 'precio_sugerido' => 150],
            ['estado_pieza' => 'corona', 'nombre_legible' => 'Corona dental', 'precio_sugerido' => 1200],
            ['estado_pieza' => 'endodoncia', 'nombre_legible' => 'Endodoncia', 'precio_sugerido' => 900],
            ['estado_pieza' => 'extraccion', 'nombre_legible' => 'Extracción dental', 'precio_sugerido' => 350],
        ];

        foreach ($tarifas as $tarifa) {
            TarifaTratamiento::updateOrCreate(
                ['estado_pieza' => $tarifa['estado_pieza']],
                [
                    'nombre_legible' => $tarifa['nombre_legible'],
                    'precio_sugerido' => $tarifa['precio_sugerido'],
                    'activo' => true,
                ]
            );
        }
    }
}
