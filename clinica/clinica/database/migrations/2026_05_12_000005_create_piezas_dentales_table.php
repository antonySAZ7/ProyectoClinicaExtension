<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piezas_dentales', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('numero')->unique();
            $table->string('nombre');
            $table->unsignedTinyInteger('cuadrante');
            $table->unsignedTinyInteger('posicion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piezas_dentales');
    }
};
