<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Registro Financeiro</title>
    <style>
        :root {
            --bg: #f3f4f6;
            --card: #ffffff;
            --primary: #0b3c5d;
            --text: #111827;
            --muted: #6b7280;
            --danger: #b91c1c;
            --border: #d1d5db;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 16px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f9fafb, #e5edf4);
            color: var(--text);
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        }

        h1 {
            margin: 0 0 8px;
            color: var(--primary);
            font-size: 1.4rem;
        }

        p {
            margin: 0 0 18px;
            color: var(--muted);
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        input:focus {
            outline: 2px solid #bfdbfe;
            border-color: #93c5fd;
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            color: #fff;
            font-weight: 700;
            background: var(--primary);
            cursor: pointer;
        }

        .error {
            margin: 0 0 12px;
            color: var(--danger);
            font-size: 0.9rem;
        }

        .hint {
            margin-top: 14px;
            color: var(--muted);
            font-size: 0.85rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Acesso ao sistema</h1>
        <p>Entre com seu login para visualizar os lancamentos.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="login">Login</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus>

            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Entrar</button>
        </form>

        <div class="hint">Usuario inicial: admin | Senha: 123456</div>
    </div>
</body>
</html>
