<?php

namespace App\Policies;

use App\Models\Lancamento;
use App\Models\User;

class LancamentoPolicy
{
    public function update(User $user, Lancamento $lancamento): bool
    {
        return $user->id === $lancamento->user_id;
    }

    public function delete(User $user, Lancamento $lancamento): bool
    {
        return $user->id === $lancamento->user_id;
    }
}
