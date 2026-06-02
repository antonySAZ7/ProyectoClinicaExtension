<?php

namespace App\Mail;

use App\Models\Consulta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsultaCerradaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Consulta $consulta)
    {
        $this->consulta->loadMissing([
            'paciente.consultas.presupuestoItems',
            'paciente.pagos',
            'observaciones',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resumen de tu consulta'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consultas.cerrada'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
