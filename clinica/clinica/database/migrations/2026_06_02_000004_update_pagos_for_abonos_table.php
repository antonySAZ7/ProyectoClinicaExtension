<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL bloquea dropear un unique index si una FK lo está usando como
        // índice de soporte. Hay que soltar la FK primero, luego el unique, y
        // recrear la FK (que generará su propio índice no-unique).
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['cita_id']);
            $table->dropUnique('pagos_cita_id_unique');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('cita_id')->nullable()->change();
            $table->foreign('cita_id')->references('id')->on('citas')->cascadeOnDelete();

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
            $table->dropForeign(['cita_id']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('cita_id')->nullable(false)->change();
            $table->unique('cita_id');
            $table->foreign('cita_id')->references('id')->on('citas')->cascadeOnDelete();
        });
    }
};
