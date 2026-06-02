<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropUnique('pagos_cita_id_unique');
            $table->unsignedBigInteger('cita_id')->nullable()->change();
            $table->foreignId('consulta_id')
                ->nullable()
                ->after('cita_id')
                ->constrained('consultas')
                ->nullOnDelete();
            $table->text('notas')->nullable()->after('fecha_pago');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consulta_id');
            $table->dropColumn('notas');
            $table->unsignedBigInteger('cita_id')->nullable(false)->change();
            $table->unique('cita_id');
        });
    }
};
