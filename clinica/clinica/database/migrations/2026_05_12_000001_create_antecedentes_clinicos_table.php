<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antecedentes_clinicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->unique('paciente_id'); // 1:1

            // Anamnesis
            $table->date('ultima_visita_dental')->nullable();
            $table->string('ultima_visita_motivo')->nullable();
            $table->boolean('presento_complicacion')->nullable();
            $table->boolean('en_tratamiento_medico')->nullable();
            $table->string('tratamiento_enfermedad')->nullable();
            $table->boolean('toma_medicamento')->nullable();
            $table->string('cual_medicamento')->nullable();
            $table->boolean('alergico_medicamento')->nullable();
            $table->string('cuales_medicamentos')->nullable();
            $table->string('otro_antecedente')->nullable();
            $table->text('descripcion_enfermedades')->nullable();

            // Antecedentes medicos (14)
            $table->boolean('ant_cardiovascular')->nullable();
            $table->boolean('ant_diabetes')->nullable();
            $table->boolean('ant_endocrino')->nullable();
            $table->boolean('ant_renal')->nullable();
            $table->boolean('ant_alergias')->nullable();
            $table->boolean('ant_hepatitis')->nullable();
            $table->boolean('ant_embarazo')->nullable();
            $table->boolean('ant_convulsiones')->nullable();
            $table->boolean('ant_venereas')->nullable();
            $table->boolean('ant_fiebre_reumatica')->nullable();
            $table->boolean('ant_tuberculosis')->nullable();
            $table->boolean('ant_hemorragias')->nullable();
            $table->boolean('ant_discrasias')->nullable();
            $table->boolean('ant_otras_medicas')->nullable();

            // Antecedentes odontologicos (7)
            $table->boolean('odo_hemorragia')->nullable();
            $table->boolean('odo_infecciones')->nullable();
            $table->boolean('odo_ulceras')->nullable();
            $table->boolean('odo_reaccion_anestesia')->nullable();
            $table->boolean('odo_dolor_dentario')->nullable();
            $table->boolean('odo_sensibilidad')->nullable();
            $table->boolean('odo_otras')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antecedentes_clinicos');
    }
};
