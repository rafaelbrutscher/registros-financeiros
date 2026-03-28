<?php

namespace Database\Seeders;

use App\Models\Lancamento;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'login' => 'admin',
        ], [
            'nome' => 'Administrador Financeiro',
            'senha' => '123456',
            'situacao' => 'ATIVO',
        ]);

        $lancamentos = [
            [
                'descricao' => 'Salario mensal',
                'data_lancamento' => '2026-03-05',
                'valor' => 5500.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'PAGO',
            ],
            [
                'descricao' => 'Aluguel',
                'data_lancamento' => '2026-03-10',
                'valor' => 1800.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PAGO',
            ],
            [
                'descricao' => 'Internet',
                'data_lancamento' => '2026-03-12',
                'valor' => 130.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PENDENTE',
            ],
            [
                'descricao' => 'Freelance',
                'data_lancamento' => '2026-03-18',
                'valor' => 950.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'RECEBIDO',
            ],
                        [
                'descricao' => 'Jogo do bicho',
                'data_lancamento' => '2026-03-05',
                'valor' => 100.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PAGO',
            ],
                        [
                'descricao' => 'Pensão',
                'data_lancamento' => '2026-03-05',
                'valor' => 1000.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PAGO',
            ],
                        [
                'descricao' => 'Apostas esportivas',
                'data_lancamento' => '2026-03-05',
                'valor' => 5700.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PAGO',
            ],
                        [
                'descricao' => 'Vendas casadas',
                'data_lancamento' => '2026-03-05',
                'valor' => 900.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'PAGO',
            ],
                        [
                'descricao' => 'Negociação de dívidas',
                'data_lancamento' => '2026-03-05',
                'valor' => 8500.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'PAGO',
            ],
                        [
                'descricao' => 'Bônus',
                'data_lancamento' => '2026-03-05',
                'valor' => 500.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'PAGO',
            ],
        ];

        foreach ($lancamentos as $lancamento) {
            Lancamento::query()->firstOrCreate([
                'descricao' => $lancamento['descricao'],
                'data_lancamento' => $lancamento['data_lancamento'],
                'valor' => $lancamento['valor'],
                'tipo_lancamento' => $lancamento['tipo_lancamento'],
            ], [
                'situacao' => $lancamento['situacao'],
            ]);
        }
    }
}
