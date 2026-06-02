<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_tratamientos', function (Blueprint $table) {
            $table->id();
            $table->string('estado_pieza', 40)->unique();
            $table->string('nombre_legible');
            $table->decimal('precio_sugerido', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_tratamientos');
    }
};
