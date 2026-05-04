<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita)
    {
        $this->cita->loadMissing('paciente');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio de cita'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.citas.reminder'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
