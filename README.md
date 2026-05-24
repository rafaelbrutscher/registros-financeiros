# Registros Financeiros

Sistema web em Laravel para controle de receitas e despesas.

## Funcionalidades

- Login por usuario (`login`) e senha
- Listagem de lancamentos financeiros
- Resumo com total de receitas, despesas e saldo
- Filtros por data inicial, data final e situacao
- Cadastro de lancamento
- Edicao de lancamento
- Exportacao da listagem para PDF
- Envio de e-mail quando um lancamento e criado ou atualizado

## Requisitos

- PHP 8.3.x
- Composer 2.x
- MySQL ou MariaDB
- Extensoes PHP comuns do Laravel (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`)

## Instalacao Local

1. Instale dependencias:

```bash
composer install
npm install
```

2. Crie e configure o arquivo `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure banco e e-mail no `.env`.

4. Rode migracoes:

```bash
php artisan migrate
```

5. Inicie a aplicacao:

```bash
composer run dev
```

## Variaveis de Ambiente Importantes

No arquivo `.env`, revise pelo menos:

- `APP_URL`
- `DB_*`
- `MAIL_*`
- `LANCAMENTO_NOTIFICATION_EMAIL`

### E-mail de notificacao

As notificacoes de criacao/atualizacao de lancamento usam:

1. `LANCAMENTO_NOTIFICATION_EMAIL`
2. fallback para `MAIL_FROM_ADDRESS`

Se quiser receber no seu e-mail proprio, defina:

```env
LANCAMENTO_NOTIFICATION_EMAIL="seu-email@dominio.com"
```

## Rotas Principais

- `GET /login`
- `POST /login`
- `GET /lancamentos`
- `GET /lancamentos/criar`
- `POST /lancamentos`
- `GET /lancamentos/{lancamento}/editar`
- `PUT /lancamentos/{lancamento}`
- `GET /lancamentos/exportar-pdf`

## Testes

Existe uma suite dedicada para o fluxo de lancamentos com 20 cenarios:

```bash
php artisan test --filter=LancamentoControllerTest
```

## Deploy (Producao)

### Comandos recomendados

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Regra importante

- Nao rode `composer update` em producao.
- Atualize dependencias em ambiente de desenvolvimento/homologacao.
- Commite sempre `composer.json` e `composer.lock` juntos quando alterar pacotes.

## Nota sobre compatibilidade de PHP

Este projeto usa `config.platform.php` no `composer.json` para fixar resolucao em PHP `8.3.6`.

Isso evita gerar `composer.lock` com pacotes que exigem PHP 8.4+ quando o servidor ainda esta em 8.3.x.

## Troubleshooting

### Erro: lock com pacotes incompativeis com PHP 8.3

Sintoma comum: mensagens de pacote `symfony/*` exigindo PHP 8.4.

Causa: `composer.lock` gerado em ambiente com versao de PHP diferente.

Correcao:

1. Garanta `config.platform.php` em `composer.json`.
2. Rode `composer update` em ambiente de desenvolvimento.
3. Commite o novo `composer.lock`.
4. Em producao, use somente `composer install`.

### Erro de permissao em `vendor`

Sintoma comum:

`vendor/masterminds does not exist and could not be created`

Correcao:

```bash
sudo chown -R $USER:$USER ~/registros-financeiros
rm -rf vendor
composer install --no-dev --optimize-autoloader
```

## HTTPS

Migrar para HTTPS em producao e simples na maioria dos cenarios:

1. Configurar certificado (ex.: Let's Encrypt)
2. Ajustar servidor web/proxy para redirecionar HTTP -> HTTPS
3. Atualizar `APP_URL` para `https://...`
4. Limpar/cachear configuracoes do Laravel

## Licenca

Projeto interno. Ajuste este bloco conforme a politica da equipe.
