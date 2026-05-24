<?php

namespace App\Services;

use App\Exceptions\LancamentoJaPagoException;
use App\Models\Lancamento;
use Illuminate\Support\Collection;

class LancamentoService
{
    public function calcularSaldo(Collection $lancamentos): float
    {
        $receitas = $lancamentos->where('tipo_lancamento', 'RECEITA')->sum('valor');
        $despesas = $lancamentos->where('tipo_lancamento', 'DESPESA')->sum('valor');

        return (float) ($receitas - $despesas);
    }

    public function marcarComoPago(Lancamento $lancamento): void
    {
        if ($lancamento->situacao === 'PAGO') {
            throw new LancamentoJaPagoException();
        }

        $lancamento->situacao = 'PAGO';
        $lancamento->save();
    }
}
