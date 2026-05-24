<?php

namespace Tests\Unit;

use App\Models\Lancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LancamentoModelTest extends TestCase
{
    use RefreshDatabase;

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function test_scope_pendentes_retorna_so_pendentes(): void
    {
        Lancamento::factory()->create(['situacao' => 'PENDENTE']);
        Lancamento::factory()->create(['situacao' => 'PAGO']);
        Lancamento::factory()->create(['situacao' => 'RECEBIDO']);

        $result = Lancamento::pendentes()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('PENDENTE', $result->first()->situacao);
    }

    public function test_scope_receitas_retorna_so_receitas(): void
    {
        Lancamento::factory()->create(['tipo_lancamento' => 'RECEITA']);
        Lancamento::factory()->create(['tipo_lancamento' => 'DESPESA']);
        Lancamento::factory()->create(['tipo_lancamento' => 'DESPESA']);

        $result = Lancamento::receitas()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('RECEITA', $result->first()->tipo_lancamento);
    }

    // ── Accessors / Mutator ──────────────────────────────────────────────────

    public function test_accessor_valor_formatado(): void
    {
        $lancamento = new Lancamento();
        $lancamento->valor = 12345.67;

        $this->assertEquals('R$ 12.345,67', $lancamento->valor_formatado);
    }

    public function test_mutator_valor_aceita_formato_brasileiro(): void
    {
        $lancamento = new Lancamento();
        $lancamento->valor = '1.234,56';

        $this->assertEquals(1234.56, $lancamento->valor);
    }

    public function test_accessor_is_atrasado_retorna_true_quando_pendente_e_passado(): void
    {
        $lancamento = new Lancamento([
            'situacao' => 'PENDENTE',
            'data_lancamento' => now()->subDay()->toDateString(),
        ]);

        $this->assertTrue($lancamento->is_atrasado);
    }
}
