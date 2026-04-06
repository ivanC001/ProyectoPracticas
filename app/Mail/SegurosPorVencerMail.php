<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SegurosPorVencerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $seguros
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerta de vencimiento de seguros de tractos'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seguros-alerta'
        );
    }
}
