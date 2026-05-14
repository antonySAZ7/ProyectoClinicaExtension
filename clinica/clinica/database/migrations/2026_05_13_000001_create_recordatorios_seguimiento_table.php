<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios_seguimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->string('modo', 40);
            $table->unsignedSmallInteger('intervalo_meses')->nullable();
            $table->date('fecha_objetivo');
            $table->json('dias_antes');
            $table->text('mensaje')->nullable();
            $table->json('fechas_enviadas')->nullable();
            $table->timestamp('ultimo_envio_at')->nullable();
            $table->timestamps();

            $table->index(['activo', 'fecha_objetivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_seguimiento');
    }
};
