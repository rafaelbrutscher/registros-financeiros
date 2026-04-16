<?php

namespace App\Contracts;

use App\Models\Lancamento;
use Illuminate\Support\Collection;

interface LancamentoRepository
{
    public function all(array $filters = []): Collection;

    public function create(array $data): Lancamento;

    public function update(Lancamento $lancamento, array $data): Lancamento;
}