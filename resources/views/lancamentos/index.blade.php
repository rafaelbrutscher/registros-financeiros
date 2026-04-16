<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Despesas e Receitas</title>
    <style>
        :root {
            --bg-start: #f4f1ea;
            --bg-end: #dce5ef;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --income: #0f766e;
            --expense: #b91c1c;
            --primary: #0b3c5d;
            --primary-soft: #dbeafe;
            --secondary: #475569;
            --border: #d1d5db;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, #fff8e8 0%, transparent 40%), linear-gradient(135deg, var(--bg-start), var(--bg-end));
            padding: 28px 16px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            animation: reveal 0.6s ease-out;
        }

        .header {
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header h1 {
            margin: 0;
            color: var(--primary);
            letter-spacing: 0.3px;
        }

        .header p {
            margin: 8px 0 0;
            color: var(--muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-link,
        .logout-btn,
        .filter-button {
            border: 0;
            border-radius: 10px;
            background: #1f2937;
            color: #fff;
            font-weight: 700;
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-link.secondary {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .action-link.ghost,
        .filter-button.ghost {
            background: var(--secondary);
        }

        .notice {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .panel {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(209, 213, 219, 0.9);
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
            padding: 18px;
            margin-bottom: 18px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }

        .field label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .field input,
        .field select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.95rem;
            background: #fff;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .stat-card span {
            display: block;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .stat-card strong {
            font-size: 1.2rem;
        }

        .income {
            color: var(--income);
        }

        .expense {
            color: var(--expense);
        }

        .table-wrap {
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(90deg, #0b3c5d, #145f8f);
            color: #ffffff;
        }

        th,
        td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #eef2f7;
            font-size: 0.95rem;
        }

        tbody tr {
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        .chip {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .chip-receita {
            background: #d1fae5;
            color: #065f46;
        }

        .chip-despesa {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .table-action {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .empty {
            padding: 26px;
            text-align: center;
            color: var(--muted);
        }

        @keyframes reveal {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .filters {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .table-wrap {
                overflow-x: auto;
            }

            th,
            td {
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Registro de Despesas e Receitas</h1>
                <p>Listagem de lancamentos cadastrados</p>
            </div>
            <div class="header-actions">
                <a class="action-link secondary" href="{{ route('lancamentos.create') }}">Novo lancamento</a>
                <a class="action-link" href="{{ route('lancamentos.export-pdf', request()->query()) }}">Exportar PDF</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-btn" type="submit">Sair</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <div class="panel">
            <form class="filters" method="GET" action="{{ route('lancamentos.index') }}">
                <div class="field">
                    <label for="data_inicio">Data inicial</label>
                    <input id="data_inicio" name="data_inicio" type="date" value="{{ $filters['data_inicio'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="data_fim">Data final</label>
                    <input id="data_fim" name="data_fim" type="date" value="{{ $filters['data_fim'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="situacao">Situacao</label>
                    <select id="situacao" name="situacao">
                        <option value="">Todas</option>
                        @foreach ($situacoes as $situacao)
                            <option value="{{ $situacao }}" @selected(($filters['situacao'] ?? '') === $situacao)>{{ $situacao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="display:flex; gap:10px;">
                    <button class="filter-button" type="submit">Filtrar</button>
                    <a class="filter-button ghost" href="{{ route('lancamentos.index') }}">Limpar</a>
                </div>
            </form>
        </div>

        <div class="stats">
            <div class="stat-card">
                <span>Total de receitas</span>
                <strong class="income">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</strong>
            </div>
            <div class="stat-card">
                <span>Total de despesas</span>
                <strong class="expense">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</strong>
            </div>
            <div class="stat-card">
                <span>Saldo</span>
                <strong class="{{ $saldo >= 0 ? 'income' : 'expense' }}">R$ {{ number_format($saldo, 2, ',', '.') }}</strong>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descricao</th>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Situacao</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lancamentos as $lancamento)
                        <tr>
                            <td>{{ $lancamento->id }}</td>
                            <td>{{ $lancamento->descricao }}</td>
                            <td>{{ $lancamento->data_lancamento->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format((float) $lancamento->valor, 2, ',', '.') }}</td>
                            <td>
                                <span class="chip {{ $lancamento->tipo_lancamento === 'RECEITA' ? 'chip-receita' : 'chip-despesa' }}">
                                    {{ $lancamento->tipo_lancamento }}
                                </span>
                            </td>
                            <td>{{ $lancamento->situacao }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="table-action" href="{{ route('lancamentos.edit', $lancamento) }}">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">Nenhum lancamento cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
