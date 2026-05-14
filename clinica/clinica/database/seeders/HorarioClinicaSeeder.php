<?php

namespace Database\Seeders;

use App\Models\HorarioClinica;
use Illuminate\Database\Seeder;

class HorarioClinicaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 5) as $dia) {
            HorarioClinica::updateOrCreate(
                ['dia_semana' => $dia],
                [
                    'hora_apertura' => '08:00',
                    'hora_cierre' => '17:00',
                    'activo' => true,
                ]
            );
        }

        HorarioClinica::updateOrCreate(
            ['dia_semana' => 6],
            [
                'hora_apertura' => '08:00',
                'hora_cierre' => '12:00',
                'activo' => true,
            ]
        );

        HorarioClinica::updateOrCreate(
            ['dia_semana' => 0],
            [
                'hora_apertura' => '08:00',
                'hora_cierre' => '12:00',
                'activo' => false,
            ]
        );
    }
}
