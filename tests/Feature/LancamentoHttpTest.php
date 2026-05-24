<?php

namespace Tests\Feature;

use App\Models\Lancamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LancamentoHttpTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'descricao' => 'Aluguel',
            'data_lancamento' => '2026-05-01',
            'valor' => 1500.00,
            'tipo_lancamento' => 'DESPESA',
            'situacao' => 'PAGO',
        ], $overrides);
    }

    // Test 12
    public function test_listagem_autenticado_retorna_200_e_exibe_lancamentos_do_usuario(): void
    {
        $user = User::factory()->create();
        Lancamento::factory()->create(['user_id' => $user->id, 'descricao' => 'Meu lançamento']);

        $response = $this->actingAs($user)->get(route('lancamentos.index'));

        $response->assertOk();
        $response->assertSee('Meu lançamento');
    }

    // Test 13
    public function test_rota_inexistente_retorna_404(): void
    {
        $response = $this->get('/rota-que-nao-existe');

        $response->assertNotFound();
    }

    // Test 14
    public function test_store_com_payload_valido_cria_registro_e_redireciona_com_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lancamentos.store'), $this->validPayload());

        $response->assertRedirect(route('lancamentos.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('lancamentos', ['descricao' => 'Aluguel', 'user_id' => $user->id]);
    }

    // Test 15
    public function test_store_com_valor_vazio_retorna_422_com_erro_no_campo_valor(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(
            route('lancamentos.store'),
            $this->validPayload(['valor' => ''])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['valor']);
    }

    // Test 16
    public function test_update_atualiza_registro_do_proprio_usuario(): void
    {
        $user = User::factory()->create();
        $lancamento = Lancamento::factory()->create(['user_id' => $user->id, 'descricao' => 'Original']);

        $response = $this->actingAs($user)->put(
            route('lancamentos.update', $lancamento),
            $this->validPayload(['descricao' => 'Atualizado'])
        );

        $response->assertRedirect(route('lancamentos.index'));
        $this->assertDatabaseHas('lancamentos', ['id' => $lancamento->id, 'descricao' => 'Atualizado']);
    }

    // Test 17
    public function test_destroy_remove_lancamento(): void
    {
        $user = User::factory()->create();
        $lancamento = Lancamento::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('lancamentos.destroy', $lancamento));

        $response->assertRedirect(route('lancamentos.index'));
        $this->assertDatabaseMissing('lancamentos', ['id' => $lancamento->id]);
    }

    // Test 18
    public function test_acesso_sem_autenticacao_redireciona_para_login(): void
    {
        $response = $this->get(route('lancamentos.index'));

        $response->assertRedirect(route('login'));
    }

    // Test 19
    public function test_usuario_nao_pode_editar_lancamento_de_outro_usuario(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $lancamento = Lancamento::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->putJson(
            route('lancamentos.update', $lancamento),
            $this->validPayload()
        );

        $response->assertForbidden();
    }

    // Test 20
    public function test_healthz_retorna_status_ok_e_db_ok(): void
    {
        $response = $this->getJson('/healthz');

        $response->assertOk();
        $response->assertJson(['status' => 'ok', 'db' => 'ok']);
    }
}
