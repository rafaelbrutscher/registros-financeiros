@php
    $descricao = old('descricao', $lancamento->descricao);
    $dataLancamento = old('data_lancamento', optional($lancamento->data_lancamento)->format('Y-m-d'));
    $valor = old('valor', $lancamento->valor);
    $tipoLancamento = old('tipo_lancamento', $lancamento->tipo_lancamento);
    $situacao = old('situacao', $lancamento->situacao);
@endphp

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
        --border: #d1d5db;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text);
        background: radial-gradient(circle at top left, #fff8e8 0%, transparent 40%), linear-gradient(135deg, var(--bg-start), var(--bg-end));
        padding: 28px 16px;
    }

    .container {
        max-width: 920px;
        margin: 0 auto;
        animation: reveal 0.6s ease-out;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .header h1 { margin: 0; color: var(--primary); }
    .header p { margin: 8px 0 0; color: var(--muted); }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-link,
    .submit-button {
        border: 0;
        border-radius: 10px;
        padding: 10px 14px;
        text-decoration: none;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .action-link { background: var(--primary-soft); color: var(--primary); }
    .submit-button { background: #1f2937; color: #fff; }

    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
        padding: 20px;
    }

    .errors {
        margin-bottom: 18px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .field label {
        display: block;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.95rem;
        background: #fff;
    }

    .field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .full {
        grid-column: 1 / -1;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }

    @keyframes reveal {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 760px) {
        .grid { grid-template-columns: 1fr; }
    }
</style>

<div class="container">
    <div class="header">
        <div>
            <h1>{{ $pageTitle }}</h1>
            <p>Preencha os dados do lancamento financeiro.</p>
        </div>
        <div class="header-actions">
            <a class="action-link" href="{{ route('lancamentos.index') }}">Voltar</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="errors">
            <strong>Revise os campos abaixo.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ $formAction }}">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="grid">
                <div class="field full">
                    <label for="descricao">Descricao</label>
                    <input id="descricao" name="descricao" type="text" value="{{ $descricao }}" required>
                </div>

                <div class="field">
                    <label for="data_lancamento">Data</label>
                    <input id="data_lancamento" name="data_lancamento" type="date" value="{{ $dataLancamento }}" required>
                </div>

                <div class="field">
                    <label for="valor">Valor</label>
                    <input id="valor" name="valor" type="number" step="0.01" min="0" value="{{ $valor }}" required>
                </div>

                <div class="field">
                    <label for="tipo_lancamento">Tipo</label>
                    <select id="tipo_lancamento" name="tipo_lancamento" required>
                        <option value="">Selecione</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @selected($tipoLancamento === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="situacao">Situacao</label>
                    <select id="situacao" name="situacao" required>
                        <option value="">Selecione</option>
                        @foreach ($situacoes as $option)
                            <option value="{{ $option }}" @selected($situacao === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="actions">
                <button class="submit-button" type="submit">{{ $lancamento->exists ? 'Atualizar' : 'Salvar' }}</button>
            </div>
        </form>
    </div>
</div>