<?php

namespace App\Repositories;

use App\Contracts\LancamentoRepository;
use App\Models\Lancamento;
use Illuminate\Support\Collection;

class EloquentLancamentoRepository implements LancamentoRepository
{
    public function all(array $filters = []): Collection
    {
        return Lancamento::query()
            ->when($filters['data_inicio'] ?? null, function ($query, string $dataInicio): void {
                $query->whereDate('data_lancamento', '>=', $dataInicio);
            })
            ->when($filters['data_fim'] ?? null, function ($query, string $dataFim): void {
                $query->whereDate('data_lancamento', '<=', $dataFim);
            })
            ->when($filters['situacao'] ?? null, function ($query, string $situacao): void {
                $query->where('situacao', $situacao);
            })
            ->orderByDesc('data_lancamento')
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data): Lancamento
    {
        return Lancamento::query()->create($data);
    }

    public function update(Lancamento $lancamento, array $data): Lancamento
    {
        $lancamento->update($data);

        return $lancamento->refresh();
    }
}