<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->timestamp('presupuesto_aceptado_en')->nullable()->after('signos_otros');
            $table->foreignId('consulta_origen_id')
                ->nullable()
                ->after('cita_id')
                ->constrained('consultas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consulta_origen_id');
            $table->dropColumn('presupuesto_aceptado_en');
        });
    }
};
