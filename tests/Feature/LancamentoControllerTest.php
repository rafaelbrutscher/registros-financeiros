<?php

namespace Tests\Feature;

use App\Contracts\LancamentoRepository;
use App\Http\Controllers\LancamentoController;
use App\Mail\LancamentoNotificadoMail;
use App\Models\Lancamento;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Tests\TestCase;

class InMemoryLancamentoRepository implements LancamentoRepository
{
    public array $lastFilters = [];

    public array $lastCreated = [];

    public array $lastUpdated = [];

    public function __construct(public Collection $items)
    {
    }

    public function all(array $filters = []): Collection
    {
        $this->lastFilters = $filters;

        return $this->items
            ->filter(function (Lancamento $lancamento) use ($filters): bool {
                $dataLancamento = Carbon::parse((string) $lancamento->data_lancamento);

                if (! empty($filters['data_inicio']) && $dataLancamento->lt(Carbon::parse($filters['data_inicio']))) {
                    return false;
                }

                if (! empty($filters['data_fim']) && $dataLancamento->gt(Carbon::parse($filters['data_fim']))) {
                    return false;
                }

                if (! empty($filters['situacao']) && $lancamento->situacao !== $filters['situacao']) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('data_lancamento')
            ->sortByDesc('id')
            ->values();
    }

    public function create(array $data): Lancamento
    {
        $this->lastCreated = $data;

        return new Lancamento($data);
    }

    public function update(Lancamento $lancamento, array $data): Lancamento
    {
        $this->lastUpdated = $data;
        $lancamento->fill($data);

        return $lancamento;
    }
}

class LancamentoControllerTest extends TestCase
{
    private function controller(array $items = []): array
    {
        $repository = new InMemoryLancamentoRepository(new Collection($items));
        $controller = new LancamentoController($repository);

        return [$controller, $repository];
    }

    private function lancamento(array $attributes = []): Lancamento
    {
        $lancamento = new Lancamento(array_merge([
            'descricao' => 'Base',
            'data_lancamento' => '2026-04-10',
            'valor' => 100.00,
            'tipo_lancamento' => 'RECEITA',
            'situacao' => 'RECEBIDO',
        ], $attributes));

        $lancamento->id = $attributes['id'] ?? 1;
        $lancamento->exists = true;

        return $lancamento;
    }

    private function assertAuthMiddleware(string $routeName): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertInstanceOf(LaravelRoute::class, $route);
        $this->assertContains('auth', $route->gatherMiddleware());
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'descricao' => 'Consultoria mensal',
            'data_lancamento' => '2026-04-10',
            'valor' => 1250.50,
            'tipo_lancamento' => 'RECEITA',
            'situacao' => 'RECEBIDO',
        ], $overrides);
    }

    public function test_lancamentos_index_has_auth_middleware(): void
    {
        $this->assertAuthMiddleware('lancamentos.index');
    }

    public function test_create_route_has_auth_middleware(): void
    {
        $this->assertAuthMiddleware('lancamentos.create');
    }

    public function test_store_route_has_auth_middleware(): void
    {
        $this->assertAuthMiddleware('lancamentos.store');
    }

    public function test_edit_route_has_auth_middleware(): void
    {
        $this->assertAuthMiddleware('lancamentos.edit');
    }

    public function test_update_route_has_auth_middleware(): void
    {
        $this->assertAuthMiddleware('lancamentos.update');
    }

    public function test_pdf_route_has_auth_middleware(): void
    {
        $this->assertAuthMiddleware('lancamentos.export-pdf');
    }

    public function test_index_returns_view_with_totals(): void
    {
        [$controller, $repository] = $this->controller([
            $this->lancamento(['descricao' => 'Receita', 'tipo_lancamento' => 'RECEITA', 'valor' => 1500]),
            $this->lancamento(['descricao' => 'Despesa', 'tipo_lancamento' => 'DESPESA', 'valor' => 400]),
        ]);

        $view = $controller->index(Request::create('/lancamentos', 'GET'));

        $this->assertSame('lancamentos.index', $view->name());
        $this->assertSame([], $repository->lastFilters);
        $this->assertEquals(1500.0, $view->getData()['totalReceitas']);
        $this->assertEquals(400.0, $view->getData()['totalDespesas']);
        $this->assertEquals(1100.0, $view->getData()['saldo']);
    }

    public function test_index_filters_records_by_status(): void
    {
        [$controller, $repository] = $this->controller([
            $this->lancamento(['descricao' => 'Pago', 'situacao' => 'PAGO']),
            $this->lancamento(['descricao' => 'Pendente', 'situacao' => 'PENDENTE']),
        ]);

        $view = $controller->index(Request::create('/lancamentos', 'GET', ['situacao' => 'PENDENTE']));

        $this->assertSame('PENDENTE', $repository->lastFilters['situacao']);
        $this->assertSame(['Pendente'], $view->getData()['lancamentos']->pluck('descricao')->all());
    }

    public function test_index_filters_records_by_start_date(): void
    {
        [$controller, $repository] = $this->controller([
            $this->lancamento(['descricao' => 'Old', 'data_lancamento' => '2026-03-30']),
            $this->lancamento(['descricao' => 'Recent', 'data_lancamento' => '2026-04-12']),
        ]);

        $controller->index(Request::create('/lancamentos', 'GET', ['data_inicio' => '2026-04-01']));

        $this->assertSame('2026-04-01', $repository->lastFilters['data_inicio']);
    }

    public function test_index_filters_records_by_end_date(): void
    {
        [$controller, $repository] = $this->controller([
            $this->lancamento(['descricao' => 'Early', 'data_lancamento' => '2026-04-01']),
            $this->lancamento(['descricao' => 'Late', 'data_lancamento' => '2026-04-20']),
        ]);

        $controller->index(Request::create('/lancamentos', 'GET', ['data_fim' => '2026-04-10']));

        $this->assertSame('2026-04-10', $repository->lastFilters['data_fim']);
    }

    public function test_index_filters_records_by_date_range(): void
    {
        [$controller, $repository] = $this->controller([
            $this->lancamento(['descricao' => 'Before', 'data_lancamento' => '2026-04-01']),
            $this->lancamento(['descricao' => 'Inside 1', 'data_lancamento' => '2026-04-10']),
            $this->lancamento(['descricao' => 'Inside 2', 'data_lancamento' => '2026-04-12']),
            $this->lancamento(['descricao' => 'After', 'data_lancamento' => '2026-04-20']),
        ]);

        $controller->index(Request::create('/lancamentos', 'GET', [
            'data_inicio' => '2026-04-09',
            'data_fim' => '2026-04-15',
        ]));

        $this->assertSame('2026-04-09', $repository->lastFilters['data_inicio']);
        $this->assertSame('2026-04-15', $repository->lastFilters['data_fim']);
    }

    public function test_index_totals_follow_the_returned_collection(): void
    {
        [$controller] = $this->controller([
            $this->lancamento(['descricao' => 'Receita 1', 'tipo_lancamento' => 'RECEITA', 'valor' => 1000]),
            $this->lancamento(['descricao' => 'Receita 2', 'tipo_lancamento' => 'RECEITA', 'valor' => 250]),
            $this->lancamento(['descricao' => 'Despesa', 'tipo_lancamento' => 'DESPESA', 'valor' => 125]),
        ]);

        $view = $controller->index(Request::create('/lancamentos', 'GET'));

        $this->assertEquals(1250.0, $view->getData()['totalReceitas']);
        $this->assertEquals(125.0, $view->getData()['totalDespesas']);
        $this->assertEquals(1125.0, $view->getData()['saldo']);
    }

    public function test_create_returns_form_view(): void
    {
        [$controller] = $this->controller();

        $view = $controller->create();

        $this->assertSame('lancamentos.create', $view->name());
        $this->assertSame('POST', $view->getData()['formMethod']);
    }

    public function test_edit_returns_form_view(): void
    {
        [$controller] = $this->controller();
        $lancamento = $this->lancamento(['id' => 42, 'descricao' => 'Editar']);

        $view = $controller->edit($lancamento);

        $this->assertSame('lancamentos.edit', $view->name());
        $this->assertSame('PUT', $view->getData()['formMethod']);
    }

    public function test_store_creates_and_notifies(): void
    {
        Mail::fake();
        [$controller, $repository] = $this->controller();

        $response = $controller->store(Request::create('/lancamentos', 'POST', $this->validPayload()));

        $this->assertSame('Consultoria mensal', $repository->lastCreated['descricao']);
        $this->assertSame(route('lancamentos.index'), $response->getTargetUrl());
        Mail::assertSent(LancamentoNotificadoMail::class, function (LancamentoNotificadoMail $mail) {
            return $mail->acao === 'criado' && $mail->lancamento->descricao === 'Consultoria mensal';
        });
    }

    public function test_store_requires_descricao(): void
    {
        [$controller] = $this->controller();

        $this->expectException(ValidationException::class);
        $controller->store(Request::create('/lancamentos', 'POST', $this->validPayload(['descricao' => ''])));
    }

    public function test_update_persists_and_notifies(): void
    {
        Mail::fake();
        [$controller, $repository] = $this->controller();
        $lancamento = $this->lancamento(['id' => 7, 'descricao' => 'Original', 'tipo_lancamento' => 'RECEITA']);

        $controller->update(Request::create('/lancamentos/7', 'PUT', $this->validPayload([
            'descricao' => 'Atualizado',
            'tipo_lancamento' => 'DESPESA',
            'situacao' => 'PAGO',
        ])), $lancamento);

        $this->assertSame('Atualizado', $repository->lastUpdated['descricao']);
        Mail::assertSent(LancamentoNotificadoMail::class, function (LancamentoNotificadoMail $mail) {
            return $mail->acao === 'atualizado' && $mail->lancamento->descricao === 'Atualizado';
        });
    }

    public function test_update_requires_tipo_lancamento(): void
    {
        [$controller] = $this->controller();
        $lancamento = $this->lancamento(['id' => 8, 'descricao' => 'Original']);

        $this->expectException(ValidationException::class);
        $controller->update(Request::create('/lancamentos/8', 'PUT', $this->validPayload(['tipo_lancamento' => ''])), $lancamento);
    }

    public function test_export_pdf_returns_pdf_response(): void
    {
        [$controller] = $this->controller([
            $this->lancamento(['descricao' => 'Receita', 'tipo_lancamento' => 'RECEITA', 'valor' => 200]),
        ]);

        $response = $controller->exportPdf(Request::create('/lancamentos/exportar-pdf', 'GET'));

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="lancamentos.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function test_export_pdf_applies_filters_to_repository(): void
    {
        [$controller, $repository] = $this->controller([
            $this->lancamento(['descricao' => 'Dentro', 'situacao' => 'PENDENTE', 'data_lancamento' => '2026-04-10']),
        ]);

        $controller->exportPdf(Request::create('/lancamentos/exportar-pdf', 'GET', [
            'situacao' => 'PENDENTE',
            'data_inicio' => '2026-04-01',
        ]));

        $this->assertSame('PENDENTE', $repository->lastFilters['situacao']);
        $this->assertSame('2026-04-01', $repository->lastFilters['data_inicio']);
    }
}