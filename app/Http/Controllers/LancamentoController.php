<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use Illuminate\View\View;

class LancamentoController extends Controller
{
    public function index(): View
    {
        $lancamentos = Lancamento::query()
            ->orderByDesc('data_lancamento')
            ->orderByDesc('id')
            ->get();

        $totalReceitas = $lancamentos
            ->where('tipo_lancamento', 'RECEITA')
            ->sum('valor');

        $totalDespesas = $lancamentos
            ->where('tipo_lancamento', 'DESPESA')
            ->sum('valor');

        return view('lancamentos.index', [
            'lancamentos' => $lancamentos,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $totalReceitas - $totalDespesas,
        ]);
    }
}
