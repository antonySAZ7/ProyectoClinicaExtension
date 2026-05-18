<?php

namespace App\Mail;

use App\Models\RecordatorioSeguimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SeguimientoReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RecordatorioSeguimiento $recordatorio)
    {
        $this->recordatorio->loadMissing(['paciente', 'cita.servicio']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: '.$this->recordatorio->displayTitle()
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.citas.seguimiento-reminder'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
