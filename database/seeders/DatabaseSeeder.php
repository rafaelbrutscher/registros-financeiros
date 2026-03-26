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
