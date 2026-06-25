<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class AntecedenteClinico extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'antecedentes_clinicos';

    /**
     * Antecedentes médicos (Sí/No). campo => etiqueta.
     */
    public const CAMPOS_MEDICOS = [
        'ant_cardiovascular' => 'Enfermedad cardiovascular',
        'ant_diabetes' => 'Diabetes',
        'ant_endocrino' => 'Problemas endocrinos',
        'ant_renal' => 'Problemas renales',
        'ant_alergias' => 'Alergias',
        'ant_hepatitis' => 'Hepatitis',
        'ant_embarazo' => 'Embarazo',
        'ant_convulsiones' => 'Convulsiones',
        'ant_venereas' => 'Enfermedades venéreas',
        'ant_fiebre_reumatica' => 'Fiebre reumática',
        'ant_tuberculosis' => 'Tuberculosis',
        'ant_hemorragias' => 'Hemorragias',
        'ant_discrasias' => 'Discrasias sanguíneas',
        'ant_otras_medicas' => 'Otras enfermedades',
    ];

    /**
     * Antecedentes odontológicos (Sí/No). campo => etiqueta.
     */
    public const CAMPOS_ODONTOLOGICOS = [
        'odo_hemorragia' => 'Hemorragia',
        'odo_infecciones' => 'Infecciones',
        'odo_ulceras' => 'Úlceras',
        'odo_reaccion_anestesia' => 'Reacción a la anestesia',
        'odo_dolor_dentario' => 'Dolor dentario',
        'odo_sensibilidad' => 'Sensibilidad',
        'odo_otras' => 'Otras enfermedades',
    ];

    /**
     * Campos booleanos de anamnesis. campo => etiqueta.
     */
    public const CAMPOS_ANAMNESIS_BOOL = [
        'presento_complicacion' => 'El paciente presentó alguna complicación en su última visita',
        'en_tratamiento_medico' => 'El paciente está siendo tratado por un médico actualmente',
        'toma_medicamento' => 'El paciente toma algún tipo de medicamento',
        'alergico_medicamento' => 'El paciente es alérgico a algún medicamento',
    ];

    /**
     * Campos de texto/fecha de anamnesis. campo => etiqueta.
     */
    public const CAMPOS_ANAMNESIS_TEXTO = [
        'ultima_visita_dental' => 'Fecha aproximada de su última visita dental',
        'ultima_visita_motivo' => 'Motivo por el cual visitó el consultorio dental',
        'tratamiento_enfermedad' => '¿Para qué enfermedad?',
        'cual_medicamento' => '¿Cuál medicamento?',
        'cuales_medicamentos' => '¿Cuál o cuáles medicamentos?',
        'otro_antecedente' => 'Otro antecedente',
        'descripcion_enfermedades' => 'Descripción de su enfermedad o enfermedades',
    ];

    protected $fillable = [
        'paciente_id',
        'ultima_visita_dental',
        'ultima_visita_motivo',
        'presento_complicacion',
        'en_tratamiento_medico',
        'tratamiento_enfermedad',
        'toma_medicamento',
        'cual_medicamento',
        'alergico_medicamento',
        'cuales_medicamentos',
        'otro_antecedente',
        'descripcion_enfermedades',
        'ant_cardiovascular',
        'ant_diabetes',
        'ant_endocrino',
        'ant_renal',
        'ant_alergias',
        'ant_hepatitis',
        'ant_embarazo',
        'ant_convulsiones',
        'ant_venereas',
        'ant_fiebre_reumatica',
        'ant_tuberculosis',
        'ant_hemorragias',
        'ant_discrasias',
        'ant_otras_medicas',
        'odo_hemorragia',
        'odo_infecciones',
        'odo_ulceras',
        'odo_reaccion_anestesia',
        'odo_dolor_dentario',
        'odo_sensibilidad',
        'odo_otras',
    ];

    protected function casts(): array
    {
        return [
            'ultima_visita_dental' => 'date',
            'presento_complicacion' => 'boolean',
            'en_tratamiento_medico' => 'boolean',
            'toma_medicamento' => 'boolean',
            'alergico_medicamento' => 'boolean',
            'ant_cardiovascular' => 'boolean',
            'ant_diabetes' => 'boolean',
            'ant_endocrino' => 'boolean',
            'ant_renal' => 'boolean',
            'ant_alergias' => 'boolean',
            'ant_hepatitis' => 'boolean',
            'ant_embarazo' => 'boolean',
            'ant_convulsiones' => 'boolean',
            'ant_venereas' => 'boolean',
            'ant_fiebre_reumatica' => 'boolean',
            'ant_tuberculosis' => 'boolean',
            'ant_hemorragias' => 'boolean',
            'ant_discrasias' => 'boolean',
            'ant_otras_medicas' => 'boolean',
            'odo_hemorragia' => 'boolean',
            'odo_infecciones' => 'boolean',
            'odo_ulceras' => 'boolean',
            'odo_reaccion_anestesia' => 'boolean',
            'odo_dolor_dentario' => 'boolean',
            'odo_sensibilidad' => 'boolean',
            'odo_otras' => 'boolean',
        ];
    }

    /**
     * Todos los campos booleanos (médicos + odontológicos + anamnesis).
     *
     * @return array<int, string>
     */
    public static function camposBooleanos(): array
    {
        return array_merge(
            array_keys(self::CAMPOS_MEDICOS),
            array_keys(self::CAMPOS_ODONTOLOGICOS),
            array_keys(self::CAMPOS_ANAMNESIS_BOOL),
        );
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
