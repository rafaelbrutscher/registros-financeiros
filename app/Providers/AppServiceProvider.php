<?php

namespace App\Providers;

use App\Contracts\LancamentoRepository;
use App\Repositories\EloquentLancamentoRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LancamentoRepository::class, EloquentLancamentoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
