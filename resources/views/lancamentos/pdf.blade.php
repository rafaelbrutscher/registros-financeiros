<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de lancamentos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        h1 {
            margin-bottom: 6px;
            color: #0b3c5d;
        }

        .meta {
            margin-bottom: 14px;
            color: #6b7280;
        }

        .stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .stats td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
        }

        table.items th,
        table.items td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
        }

        table.items th {
            background: #0b3c5d;
            color: #fff;
        }
    </style>
</head>
<body>
    <h1>Relatorio de lancamentos</h1>
    <div class="meta">
        @if (!empty($filters['data_inicio']) || !empty($filters['data_fim']) || !empty($filters['situacao']))
            Filtros aplicados:
            {{ $filters['data_inicio'] ?? 'inicio livre' }}
            ate
            {{ $filters['data_fim'] ?? 'fim livre' }}
            | Situacao: {{ $filters['situacao'] ?? 'todas' }}
        @else
            Relatorio sem filtros.
        @endif
    </div>

    <table class="stats">
        <tr>
            <td><strong>Receitas</strong><br>R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
            <td><strong>Despesas</strong><br>R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
            <td><strong>Saldo</strong><br>R$ {{ number_format($saldo, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descricao</th>
                <th>Data</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Situacao</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lancamentos as $lancamento)
                <tr>
                    <td>{{ $lancamento->id }}</td>
                    <td>{{ $lancamento->descricao }}</td>
                    <td>{{ $lancamento->data_lancamento->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format((float) $lancamento->valor, 2, ',', '.') }}</td>
                    <td>{{ $lancamento->tipo_lancamento }}</td>
                    <td>{{ $lancamento->situacao }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhum lancamento encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>