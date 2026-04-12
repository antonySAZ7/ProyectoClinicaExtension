<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Historial extends Model
{
    protected $table = 'historiales';

    protected $fillable = [
        'paciente_id',
        'descripcion',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
