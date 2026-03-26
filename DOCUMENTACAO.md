# Documentacao - Registro de Despesas e Receitas

## 1) Da aplicacao

### 1.1 Numero de classes da aplicacao

Foram consideradas as classes dentro da pasta `app`.

- Total de classes: **6**
- Classes:
  - `app/Http/Controllers/AuthController.php`
  - `app/Http/Controllers/Controller.php`
  - `app/Http/Controllers/LancamentoController.php`
  - `app/Models/Lancamento.php`
  - `app/Models/User.php`
  - `app/Providers/AppServiceProvider.php`

### 1.2 Modelagem do banco de dados (MySQL)

#### Tabela `users` (usuario)

- `id` (bigint, PK, auto incremento)
- `nome` (varchar 255)
- `login` (varchar 255, unico)
- `senha` (varchar 255)
- `situacao` (varchar 255, default `ATIVO`)
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### Tabela `lancamentos` (lancamento)

- `id` (bigint, PK, auto incremento)
- `descricao` (varchar 255)
- `data_lancamento` (date)
- `valor` (decimal 12,2)
- `tipo_lancamento` (varchar 20)
- `situacao` (varchar 20)
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### Relacao logica

- Usuario autentica no sistema via `login` e `senha`
- Usuario autenticado acessa a listagem de lancamentos

### 1.3 Interface desenvolvida

- Tela de login em Blade
  - Rota: `/login`
  - Acesso inicial: `admin` / `123456`
- Tela principal de lancamentos em Blade
  - Rota: `/lancamentos`
  - Protegida por autenticacao
- Componentes da tela principal:
  - Cards de resumo: total receitas, total despesas e saldo
  - Tabela com colunas: ID, descricao, data, valor, tipo e situacao
  - Destaque visual para tipo `RECEITA` e `DESPESA`
  - Botao de logout
  - Layout responsivo para desktop e mobile

## 2) Da publicacao (sem Docker)

### 2.1 Como acessar a VM

Exemplo de acesso por SSH:

```bash
ssh usuario@IP_DA_VM
```

### 2.2 Estrategia de instalacao

A publicacao e feita por um script unico:

- Arquivo: `scripts/setup_linux.sh`
- Objetivo: instalar linguagem, dependencias, banco, configurar ambiente, executar migrate e seed, e publicar com Nginx + PHP-FPM
- Sistema alvo: Debian/Ubuntu (apt)

### 2.3 O que o script instala e configura

O script executa automaticamente:

1. Instalacao de pacotes do sistema:
   - `git`, `curl`, `unzip`
   - `php-cli`, `php-fpm`, `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-bcmath`, `php-intl`
   - `composer`
   - `nodejs`, `npm`
   - `mysql-server`
   - `nginx`
2. Criacao/configuracao do banco MySQL
3. Configuracao do `.env` da aplicacao
4. Instalacao de dependencias PHP e front-end
5. Execucao de:
   - `php artisan key:generate --force`
   - `php artisan migrate --force`
   - `php artisan db:seed --force`
6. Ajuste de permissoes do Laravel
7. Publicacao com Nginx apontando para `public/`

### 2.4 Implantacao passo a passo

1. Clonar o repositorio na VM:

```bash
git clone <URL_DO_REPOSITORIO>
cd registro-despesas-receitas
```

2. Dar permissao de execucao no script:

```bash
chmod +x scripts/setup_linux.sh
```

3. Executar o setup completo:

```bash
sudo APP_URL=http://IP_DA_VM bash scripts/setup_linux.sh
```

4. (Opcional) sobrescrever credenciais do banco durante a execucao:

```bash
sudo APP_URL=http://IP_DA_VM \
DB_DATABASE=registro_financeiro \
DB_USERNAME=registro_user \
DB_PASSWORD=registro_pass \
bash scripts/setup_linux.sh
```

### 2.5 URL de acesso

- URL base publicada: `http://IP_DA_VM`
- Login: `http://IP_DA_VM/login`
- Sistema: `http://IP_DA_VM/lancamentos`

### 2.6 Pos-implantacao (checagem rapida)

```bash
sudo systemctl status nginx
sudo systemctl status mysql
php artisan route:list
```

## 3) Dos tempos

- Desenvolvimento da aplicacao: **65 min**
- Revisao da documentacao: **20 min**
- Criacao do script Linux de instalacao/configuracao/publicacao: **35 min**
- Publicacao da aplicacao na VM com o script: **10 min**
- Tempo total: **130 min**
