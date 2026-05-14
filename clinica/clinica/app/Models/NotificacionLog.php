<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionLog extends Model
{
    protected $table = 'notificaciones_log';

    protected $fillable = [
        'cita_id',
        'canal',
        'tipo',
        'destinatario',
        'estado',
        'payload',
        'enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'enviado_en' => 'datetime',
        ];
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }
}
