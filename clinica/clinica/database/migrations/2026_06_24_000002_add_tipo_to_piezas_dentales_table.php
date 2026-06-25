<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piezas_dentales', function (Blueprint $table) {
            $table->string('tipo')->default('permanente')->after('posicion');
            $table->index(['tipo', 'cuadrante', 'posicion']);
        });

        $temporales = [
            [55, 'Segundo molar temporal', 1, 5],
            [54, 'Primer molar temporal', 1, 4],
            [53, 'Canino temporal', 1, 3],
            [52, 'Incisivo lateral temporal', 1, 2],
            [51, 'Incisivo central temporal', 1, 1],
            [61, 'Incisivo central temporal', 2, 1],
            [62, 'Incisivo lateral temporal', 2, 2],
            [63, 'Canino temporal', 2, 3],
            [64, 'Primer molar temporal', 2, 4],
            [65, 'Segundo molar temporal', 2, 5],
            [75, 'Segundo molar temporal', 3, 5],
            [74, 'Primer molar temporal', 3, 4],
            [73, 'Canino temporal', 3, 3],
            [72, 'Incisivo lateral temporal', 3, 2],
            [71, 'Incisivo central temporal', 3, 1],
            [81, 'Incisivo central temporal', 4, 1],
            [82, 'Incisivo lateral temporal', 4, 2],
            [83, 'Canino temporal', 4, 3],
            [84, 'Primer molar temporal', 4, 4],
            [85, 'Segundo molar temporal', 4, 5],
        ];

        foreach ($temporales as [$numero, $nombre, $cuadrante, $posicion]) {
            DB::table('piezas_dentales')->updateOrInsert(
                ['numero' => $numero],
                [
                    'nombre' => $nombre,
                    'cuadrante' => $cuadrante,
                    'posicion' => $posicion,
                    'tipo' => 'temporal',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('piezas_dentales')
            ->where('tipo', 'temporal')
            ->delete();

        Schema::table('piezas_dentales', function (Blueprint $table) {
            $table->dropIndex(['tipo', 'cuadrante', 'posicion']);
            $table->dropColumn('tipo');
        });
    }
};
