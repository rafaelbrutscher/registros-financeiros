<?php

namespace App\Observers;

use App\Mail\LancamentoNotificadoMail;
use App\Models\Lancamento;
use Illuminate\Support\Facades\Mail;

class LancamentoObserver
{
    public function created(Lancamento $lancamento): void
    {
        $this->notify($lancamento, 'criado');
    }

    public function updated(Lancamento $lancamento): void
    {
        $this->notify($lancamento, 'atualizado');
    }

    private function notify(Lancamento $lancamento, string $acao): void
    {
        $recipient = config('finance.notification_email');

        if ($recipient) {
            Mail::to($recipient)->send(new LancamentoNotificadoMail($lancamento, $acao));
        }
    }
}
