<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAppointmentCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita)
    {
        $this->cita->loadMissing(['paciente', 'servicio']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva cita creada'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.cita-creada'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
