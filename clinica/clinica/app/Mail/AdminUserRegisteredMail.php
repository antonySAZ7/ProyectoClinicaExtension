<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminUserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $registeredUser)
    {
        $this->registeredUser->loadMissing('paciente');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo usuario registrado'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.usuario-registrado'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
