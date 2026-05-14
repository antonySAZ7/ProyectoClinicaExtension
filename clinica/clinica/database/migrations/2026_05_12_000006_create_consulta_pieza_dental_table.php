<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_pieza_dental', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            $table->foreignId('pieza_id')->constrained('piezas_dentales')->cascadeOnDelete();
            $table->string('estado', 40);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['consulta_id', 'pieza_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_pieza_dental');
    }
};
