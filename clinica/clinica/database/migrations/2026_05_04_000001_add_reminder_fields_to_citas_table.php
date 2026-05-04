<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->timestamp('recordatorio_enviado_at')->nullable()->after('observaciones');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE citas MODIFY estado ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE citas MODIFY estado VARCHAR(255) NOT NULL DEFAULT 'pendiente'");
        }

        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('recordatorio_enviado_at');
        });
    }
};
