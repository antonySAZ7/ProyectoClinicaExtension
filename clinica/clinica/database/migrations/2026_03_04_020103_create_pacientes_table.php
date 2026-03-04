<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('dpi')->unique();
            $table->date('fecha_nacimiento');
            $table->string('telefono');
            $table->string('correo')->unique();
            $table->string('direccion');
            $table->string('sexo')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('ocupacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
