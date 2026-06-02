<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            $table->foreignId('pieza_id')->nullable()->constrained('piezas_dentales')->nullOnDelete();
            $table->string('diagnostico');
            $table->string('tratamiento');
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->index(['consulta_id', 'pieza_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_presupuesto_items');
    }
};
