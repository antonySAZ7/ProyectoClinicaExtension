<?php

namespace Database\Seeders;

use App\Models\PiezaDental;
use Illuminate\Database\Seeder;

class PiezaDentalSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = [
            1 => 'Incisivo central',
            2 => 'Incisivo lateral',
            3 => 'Canino',
            4 => 'Primer premolar',
            5 => 'Segundo premolar',
            6 => 'Primer molar',
            7 => 'Segundo molar',
            8 => 'Tercer molar',
        ];

        foreach ([1, 2, 3, 4] as $cuadrante) {
            foreach (range(1, 8) as $posicion) {
                $numero = ($cuadrante * 10) + $posicion;

                PiezaDental::updateOrCreate(
                    ['numero' => $numero],
                    [
                        'nombre' => $nombres[$posicion],
                        'cuadrante' => $cuadrante,
                        'posicion' => $posicion,
                    ]
                );
            }
        }
    }
}
