<?php

namespace Database\Factories;

use App\Models\Lancamento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lancamento>
 */
class LancamentoFactory extends Factory
{
    protected $model = Lancamento::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'descricao' => fake()->sentence(3),
            'data_lancamento' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'valor' => fake()->randomFloat(2, 10, 9000),
            'tipo_lancamento' => fake()->randomElement(['RECEITA', 'DESPESA']),
            'situacao' => fake()->randomElement(['PAGO', 'PENDENTE', 'RECEBIDO']),
        ];
    }

    public function receita(): static
    {
        return $this->state(fn () => ['tipo_lancamento' => 'RECEITA']);
    }

    public function despesa(): static
    {
        return $this->state(fn () => ['tipo_lancamento' => 'DESPESA']);
    }

    public function pendente(): static
    {
        return $this->state(fn () => ['situacao' => 'PENDENTE']);
    }

    public function pago(): static
    {
        return $this->state(fn () => ['situacao' => 'PAGO']);
    }

    public function recebido(): static
    {
        return $this->state(fn () => ['situacao' => 'RECEBIDO']);
    }
}
