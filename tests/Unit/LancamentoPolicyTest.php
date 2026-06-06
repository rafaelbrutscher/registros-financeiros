<?php

namespace Tests\Unit;

use App\Models\Lancamento;
use App\Models\User;
use App\Policies\LancamentoPolicy;
use Tests\TestCase;

class LancamentoPolicyTest extends TestCase
{
    private LancamentoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new LancamentoPolicy;
    }

    public function test_update_permite_ao_dono_do_lancamento(): void
    {
        $user = new User;
        $user->id = 1;

        $lancamento = new Lancamento;
        $lancamento->user_id = 1;

        $this->assertTrue($this->policy->update($user, $lancamento));
    }

    public function test_update_nega_para_outro_usuario(): void
    {
        $user = new User;
        $user->id = 2;

        $lancamento = new Lancamento;
        $lancamento->user_id = 1;

        $this->assertFalse($this->policy->update($user, $lancamento));
    }
}
