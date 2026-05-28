<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->decimal('peso', 5, 2)->nullable()->after('diagnostico');
            $table->decimal('altura', 4, 2)->nullable()->after('peso');
            $table->string('presion_arterial')->nullable()->after('altura');
            $table->unsignedSmallInteger('frecuencia_cardiaca')->nullable()->after('presion_arterial');
            $table->unsignedSmallInteger('frecuencia_respiratoria')->nullable()->after('frecuencia_cardiaca');
            $table->string('signos_otros')->nullable()->after('frecuencia_respiratoria');
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropColumn([
                'peso',
                'altura',
                'presion_arterial',
                'frecuencia_cardiaca',
                'frecuencia_respiratoria',
                'signos_otros',
            ]);
        });
    }
};
