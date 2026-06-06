<?php

namespace App\Mail;

use App\Models\Lancamento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LancamentoNotificadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lancamento $lancamento,
        public string $acao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lancamento '.$this->acao,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lancamento-notificado',
        );
    }
}
