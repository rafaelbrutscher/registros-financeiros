<?php

namespace Tests\Unit;

use App\Exceptions\LancamentoJaPagoException;
use App\Models\Lancamento;
use App\Services\LancamentoService;
use Tests\TestCase;

class LancamentoServiceTest extends TestCase
{
    private LancamentoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LancamentoService;
    }

    public function test_calcular_saldo_soma_receitas_e_subtrai_despesas(): void
    {
        $lancamentos = collect([
            new Lancamento(['tipo_lancamento' => 'RECEITA', 'valor' => 1500]),
            new Lancamento(['tipo_lancamento' => 'RECEITA', 'valor' => 500]),
            new Lancamento(['tipo_lancamento' => 'DESPESA', 'valor' => 800]),
        ]);

        $this->assertEquals(1200.0, $this->service->calcularSaldo($lancamentos));
    }

    public function test_calcular_saldo_retorna_zero_sem_lancamentos(): void
    {
        $this->assertEquals(0.0, $this->service->calcularSaldo(collect()));
    }

    public function test_marcar_como_pago_lanca_excecao_se_ja_pago(): void
    {
        $lancamento = new Lancamento(['situacao' => 'PAGO']);

        $this->expectException(LancamentoJaPagoException::class);
        $this->service->marcarComoPago($lancamento);
    }
}
