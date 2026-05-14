<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->string('canal', 40);
            $table->string('tipo', 80);
            $table->string('destinatario')->nullable();
            $table->string('estado', 40);
            $table->json('payload')->nullable();
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();

            $table->index(['canal', 'tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_log');
    }
};
