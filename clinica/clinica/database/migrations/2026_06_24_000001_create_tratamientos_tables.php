<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('pieza_id')->nullable()->constrained('piezas_dentales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('estado', 32)->default('en_progreso');
            $table->date('fecha_inicio');
            $table->timestamps();

            $table->index(['paciente_id', 'estado', 'fecha_inicio'], 'tratamientos_paciente_estado_fecha_index');
        });

        Schema::create('fases_tratamiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->cascadeOnDelete();
            $table->foreignId('consulta_id')->nullable()->constrained('consultas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('descripcion');
            $table->date('fecha');
            $table->boolean('completada')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['tratamiento_id', 'orden', 'fecha'], 'fases_tratamiento_orden_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fases_tratamiento');
        Schema::dropIfExists('tratamientos');
    }
};
