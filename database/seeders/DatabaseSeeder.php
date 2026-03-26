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
        User::query()->create([
            'nome' => 'Administrador Financeiro',
            'login' => 'admin',
            'senha' => '123456',
            'situacao' => 'ATIVO',
        ]);

        Lancamento::query()->insert([
            [
                'descricao' => 'Salario mensal',
                'data_lancamento' => '2026-03-05',
                'valor' => 5500.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'PAGO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Aluguel',
                'data_lancamento' => '2026-03-10',
                'valor' => 1800.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PAGO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Internet',
                'data_lancamento' => '2026-03-12',
                'valor' => 130.00,
                'tipo_lancamento' => 'DESPESA',
                'situacao' => 'PENDENTE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Freelance',
                'data_lancamento' => '2026-03-18',
                'valor' => 950.00,
                'tipo_lancamento' => 'RECEITA',
                'situacao' => 'RECEBIDO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
