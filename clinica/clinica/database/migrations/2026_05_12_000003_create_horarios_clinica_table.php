<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_clinica', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('dia_semana')->unique();
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_clinica');
    }
};
