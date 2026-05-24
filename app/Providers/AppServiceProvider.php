<?php

namespace App\Providers;

use App\Contracts\LancamentoRepository;
use App\Models\Lancamento;
use App\Observers\LancamentoObserver;
use App\Policies\LancamentoPolicy;
use App\Repositories\EloquentLancamentoRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LancamentoRepository::class, EloquentLancamentoRepository::class);
    }

    public function boot(): void
    {
        Lancamento::observe(LancamentoObserver::class);
        Gate::policy(Lancamento::class, LancamentoPolicy::class);
    }
}
