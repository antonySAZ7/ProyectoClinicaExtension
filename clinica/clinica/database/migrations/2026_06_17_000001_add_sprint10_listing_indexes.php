<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->index('nombre_completo', 'pacientes_nombre_completo_index');
        });

        Schema::table('consultas', function (Blueprint $table) {
            $table->index(['paciente_id', 'fecha', 'created_at'], 'consultas_paciente_fecha_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropIndex('consultas_paciente_fecha_created_index');
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex('pacientes_nombre_completo_index');
        });
    }
};
