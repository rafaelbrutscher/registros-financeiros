# Briefing — Tarefa Final GCS 2026/A

Documento de contexto para a sessão do Claude Code. Mantém-se na raiz do
repositório durante toda a tarefa.

---

## Progresso

- [x] Fase 1 — Completar aplicação (21 testes) — 2026-05-24
- [x] Fase 2 — Containerização (imagem 86,5 MB) — 2026-05-24
- [x] Fase 3 — Provision VM + Mailpit — 2026-05-24
- [ ] Fase 4 — Composes Homolog/Prod + scripts up/teardown (em validação)
- [ ] Fase 5 — GitHub Actions (CI + deploys)
- [ ] Fase 6 — Documentação e roteiro dos 13 passos

---

## 0. Decisões pós-diagnóstico

- **Stack confirmada**: PHP 8.3, Laravel 13.
- **Testes**: 21 (10 Unit + 11 Feature). O enunciado pede "20 unit tests";
  a distribuição cobre o requisito com nomenclatura JUnit-compatível.
- **Mailer**: driver `log` em dev/testes; Mailpit único compartilhado entre
  Homolog e Prod, acessível via `smtp://mailpit:1025` nos containers da app.
- **Disparo de e-mail**: via `LancamentoObserver` nos eventos `created` e
  `updated` (sem chamada direta no controller).
- **CI/CD externo à VM**: GitHub Actions em runners `ubuntu-latest`.
  Deploy via SSH direto do Actions — sem self-hosted runner na VM.
- **Análise de qualidade**: Laravel Pint (style) + Larastan nível 5 (static
  analysis), ambos executados no CI como gates obrigatórios.
- **Registry**: GitHub Container Registry (`ghcr.io`). Tag `sha-<7-char>`
  para rastreabilidade; `latest` só em Homolog.

---

## 1. Objetivo

Configurar um pipeline completo de CI/CD usando GitHub como eixo central,
com deploy automatizado para dois ambientes (Homolog e Prod) em uma única
VM da Univates, cobrindo:

A. Registro da mudança — GitHub Issues  
B. Implementação — editor local  
C. Versionamento — GitHub (`main` + PRs)  
D. Testes automatizados — PHPUnit 21 testes, JUnit + Clover no Actions  
E. Análise de qualidade — Pint + Larastan no CI  
F. Build e push da imagem — GHCR via Actions  
G. Deploy automático em Homolog — SSH em push para `main`  
H. Deploy manual em Prod — `workflow_dispatch` no Actions  

**Restrições do enunciado**:
- Banco de dados versionado (Laravel Migrations)
- Tudo em contêineres
- Criação de infraestrutura automatizada (`provision.sh`)
- Processo semi-automatizado (scripts, não cliques manuais repetidos)

---

## 2. Stack

| Responsabilidade | Ferramenta | Onde roda |
|-----------------|------------|-----------|
| Registro de mudança | GitHub Issues | github.com |
| Versionamento | GitHub | github.com |
| CI (testes, lint, build) | GitHub Actions (`ubuntu-latest`) | nuvem GitHub |
| Análise de qualidade | Laravel Pint + Larastan | runner GitHub |
| Registry de imagens | GitHub Container Registry (`ghcr.io`) | nuvem GitHub |
| Artefatos de cobertura | `junit.xml` + `clover.xml` | Actions artifacts |
| App Homolog | Laravel 13 + PHP-FPM + Nginx | container na VM |
| App Prod | Laravel 13 + PHP-FPM + Nginx | container na VM |
| Banco de dados | MariaDB 11 (×2: homolog + prod) | container na VM |
| E-mail trap | Mailpit único (compartilhado) | container na VM |
| Infra VM | Docker Engine + compose plugin | Ubuntu 24.04 LTS |
| BD versionado | Laravel Migrations | nativo |

**VM Univates**: Ubuntu 24.04 LTS · 1,9 GB RAM · 1 CPU  
**IP público**: `177.44.248.118` · **Usuário SSH**: `univates`

Secrets configurados no GitHub (Settings → Secrets → Actions):  
`VM_HOST`, `VM_USER`, `VM_SSH_PRIVATE_KEY`,  
`DB_PASSWORD_HOMOLOG`, `DB_PASSWORD_PROD`,  
`APP_KEY_HOMOLOG`, `APP_KEY_PROD`

---

## 3. Estrutura-alvo do repositório

```
registros-financeiros/
├── BRIEFING.md
├── README.md
├── phpunit.xml
├── Dockerfile
├── docker-compose.yml          ← dev local (app + MariaDB + Adminer)
├── docker/
│   ├── nginx.conf
│   ├── php.ini
│   ├── php-fpm.conf
│   ├── supervisord.conf
│   └── entrypoint.sh
├── app/
├── database/
│   ├── migrations/
│   └── seeders/
├── tests/
│   ├── Unit/                   ← 10 testes
│   └── Feature/                ← 11 testes
├── .github/
│   └── workflows/
│       ├── ci.yml              ← testes + lint + build + push GHCR
│       ├── deploy-homolog.yml  ← SSH deploy automático em push main
│       └── deploy-prod.yml     ← SSH deploy manual (workflow_dispatch)
└── infra/
    ├── provision.sh            ← bootstrap idempotente da VM
    ├── teardown.sh             ← derruba tudo (demonstração ao professor)
    ├── networks.sh             ← cria homolog_net, prod_net, shared_net
    ├── .gitignore              ← ignora .env por ambiente
    ├── mailpit/
    │   └── docker-compose.yml
    ├── homolog/
    │   ├── up.sh
    │   ├── docker-compose.yml
    │   └── .env.example
    └── prod/
        ├── up.sh
        ├── docker-compose.yml
        └── .env.example
```

---

## 4. Plano de execução em fases

**Execute uma fase por vez e pare para validação humana antes da próxima.**

### Fase 1 — Completar aplicação Laravel ✅ CONCLUÍDA

Scopes, accessors, mutator, service, policy, observer, DELETE, /healthz,
phpunit.xml JUnit+Clover, 21 testes passando.

### Fase 2 — Containerização ✅ CONCLUÍDA

Dockerfile 5 stages, docker-compose.yml dev, imagem `ci` com PCOV.
Imagem final: 86,5 MB. 21/21 testes passando dentro do container.

### Fase 3 — Preparar VM via provision.sh + Mailpit

- `infra/provision.sh`: instala Docker se ausente, cria redes, sobe Mailpit
- `infra/teardown.sh`: para containers, remove volumes e redes do projeto
- `infra/networks.sh`: cria `homolog_net`, `prod_net`, `shared_net`
- `infra/mailpit/docker-compose.yml`: Mailpit UI :8025, SMTP :1025 interno
- `infra/.gitignore`: exclui arquivos `.env` em subpastas
- **Entregar**: VM limpa → `provision.sh` → Mailpit healthy, 3 redes criadas

### Fase 4 — Ambientes Homolog e Prod + scripts up/teardown

- `infra/homolog/docker-compose.yml`: app + MariaDB, porta 8090,
  redes homolog_net + shared_net
- `infra/prod/docker-compose.yml`: app + MariaDB, porta 8091,
  redes prod_net + shared_net
- `infra/homolog/up.sh` e `infra/prod/up.sh`: pull imagem, up, migrate
- Migrations rodam via `RUN_MIGRATIONS=true` no compose de ambiente
- Volumes nomeados para persistir banco entre deploys
- **Entregar**: 2 stacks funcionais, app acessível em :8090 e :8091

### Fase 5 — GitHub Actions (CI + deploys)

- `.github/workflows/ci.yml`:
  1. `checkout`
  2. PHP setup + `composer install --dev`
  3. `Pint --test` (lint gate)
  4. `Larastan analyse` (static analysis gate)
  5. `PHPUnit` — publica `junit.xml` + `clover.xml` como artifacts
  6. `docker build --target runtime`
  7. `docker push ghcr.io/<owner>/registros-financeiros:sha-<SHA>`
- `.github/workflows/deploy-homolog.yml`: disparado em push `main` →
  SSH na VM → `docker pull` + `compose up` + `migrate --force`
- `.github/workflows/deploy-prod.yml`: `workflow_dispatch` manual →
  mesma sequência no stack de prod
- **Entregar**: pipeline verde ponta a ponta com 1 commit demo

### Fase 6 — Documentação e roteiro dos 13 passos

- `README.md`: diagrama da arquitetura, comandos exatos, 13 passos
- Documento para entregar ao professor (Markdown/PDF)

---

## 5. Padrões e convenções

- **Código PHP**: PSR-12. Larastan nível 5. Pint sem diff no CI.
- **Commits**: Conventional Commits (`feat:`, `fix:`, `test:`, `chore:`,
  `ci:`, `docs:`).
- **Branches**: `main` (protegida, requer PR), features em `feat/nome`.
  Push em `main` dispara CI + deploy-homolog automaticamente.
- **Migrations**: nunca editar migration já aplicada; criar nova.
- **Variáveis sensíveis**: `.env` por ambiente, nunca commitado.
  `.env.example` em cada pasta de ambiente.
- **Tags Docker**: `sha-<7-char>` para rastreabilidade. Prod usa SHA
  explícito; Homolog pode usar `latest`.
- **Healthchecks**: todo serviço no compose tem `healthcheck`. Deploy só
  prossegue após o container ficar `healthy`.

---

## 6. Os 21 testes

### `tests/Unit/` (10)

**LancamentoModelTest** (5 testes)
1. `scopePendentes()` retorna só `situacao = PENDENTE`
2. `scopeReceitas()` retorna só `tipo_lancamento = RECEITA`
3. Accessor `valor_formatado` formata `12345.67` → `"R$ 12.345,67"`
4. Mutator `valor` aceita string `"1.234,56"` e persiste como `1234.56`
5. Accessor `is_atrasado` retorna `true` quando pendente e data < hoje

**LancamentoServiceTest** (3 testes)
6. `calcularSaldo()` soma receitas e subtrai despesas — caso feliz
7. `calcularSaldo()` retorna `0` sem lançamentos
8. `marcarComoPago()` lança `LancamentoJaPagoException` se já PAGO

**LancamentoPolicyTest** (2 testes)
9. `update()` permite ao dono do lançamento
10. `update()` nega para outro usuário

### `tests/Feature/` (11)

**ExampleTest** (2 testes)
11. `GET /` redireciona para `/login`
12. Página de login é acessível (200)

**LancamentoHttpTest** (9 testes)
13. `GET /lancamentos` autenticado retorna 200
14. `GET /rota-inexistente` retorna 404
15. `POST /lancamentos` com payload válido cria e redireciona com `success`
16. `POST /lancamentos` com valor vazio retorna 422
17. `PUT /lancamentos/{id}` atualiza registro do próprio usuário
18. `DELETE /lancamentos/{id}` remove o lançamento
19. Acesso sem autenticação redireciona para `/login`
20. Usuário B não edita lançamento de A (403 via policy)
21. `GET /healthz` retorna 200 `{"status":"ok","db":"ok"}`

---

## 7. Checklist de validação (os 13 passos do professor)

| # | Passo | Como demonstrar |
|---|-------|-----------------|
| 1 | Ambientes não existentes | `bash infra/teardown.sh` |
| 2 | Criar Homolog | `bash infra/homolog/up.sh` |
| 3 | Criar Prod | `bash infra/prod/up.sh` |
| 4 | App rodando em Homolog | Abrir `http://177.44.248.118:8090` |
| 5 | App rodando em Prod | Abrir `http://177.44.248.118:8091` |
| 6 | Registrar mudança | Criar Issue no GitHub com label `tipo:feature` ou `tipo:bug` |
| 7 | Implementar | Branch local, commit com código + migration se aplicável |
| 8 | Versionar | `git push origin feat/...` → abrir PR → merge em `main` |
| 9 | Integração | Aba Actions no GitHub: testes, Pint, Larastan, build, artifacts |
| 10 | Atualizar Homolog | `deploy-homolog.yml` dispara automaticamente em push `main` |
| 11 | Homolog com mudança | Recarregar `http://177.44.248.118:8090` |
| 12 | Atualizar Prod | Disparar `deploy-prod.yml` via `workflow_dispatch` manual |
| 13 | Prod com mudança | Recarregar `http://177.44.248.118:8091` |

---

## 8. Acessos da apresentação

Endereços que o professor pode abrir diretamente do navegador:

| Ambiente | URL |
|----------|-----|
| Homologação | http://177.44.248.118:8090 |
| Produção | http://177.44.248.118:8091 |
| Mailpit UI | http://177.44.248.118:8025 |
| Repositório | https://github.com/rafaelbrutscher/registros-financeiros |

---

## 9. Restrições importantes

- **Não use `docker-compose` (hífen)** — use `docker compose` (plugin v2).
- **Não commite secrets**. Use GitHub Secrets e injete via
  `${{ secrets.NOME }}` nos workflows.
- **Não use `latest` em Prod**. Sempre tag SHA explícita.
- **RAM da VM é 1,9 GB**. MariaDB (×2) + Mailpit + 2 PHP-FPM cabem;
  não há margem para ferramentas adicionais.
- **Deploy via SSH direto**: sem self-hosted runner. O Actions SSH na VM
  e executa `docker compose pull && up -d`.
- **Porta SMTP do Mailpit (1025) não exposta** ao host — só acessível
  dentro da `shared_net`. Somente a UI (8025) é exposta.

---

## 10. Como conduzir a sessão

A cada início de fase:
1. Resumir o que vai fazer (3-5 linhas)
2. Listar os arquivos a criar/modificar
3. Aguardar confirmação
4. Executar
5. Mostrar validação do resultado
6. Marcar fase como concluída no `## Progresso`
