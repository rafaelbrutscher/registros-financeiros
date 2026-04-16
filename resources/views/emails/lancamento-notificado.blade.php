<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lancamento {{ $acao }}</title>
</head>
<body>
    <h1>Lancamento {{ $acao }}</h1>
    <p>Um lancamento foi {{ $acao }} com os dados abaixo.</p>

    <ul>
        <li><strong>Descricao:</strong> {{ $lancamento->descricao }}</li>
        <li><strong>Data:</strong> {{ $lancamento->data_lancamento->format('d/m/Y') }}</li>
        <li><strong>Valor:</strong> R$ {{ number_format((float) $lancamento->valor, 2, ',', '.') }}</li>
        <li><strong>Tipo:</strong> {{ $lancamento->tipo_lancamento }}</li>
        <li><strong>Situacao:</strong> {{ $lancamento->situacao }}</li>
    </ul>
</body>
</html>