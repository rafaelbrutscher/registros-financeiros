#!/usr/bin/env bash
set -euo pipefail

AMBIENTE="prod"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE="${DIR}/docker-compose.yml"
ENV_FILE="${DIR}/.env"
CONTAINER="prod_app"

# ── Cores ANSI (desabilitadas quando stdout não é terminal) ───────────────────
if [ -t 1 ]; then
    GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
    RED='\033[0;31m'; NC='\033[0m'
else
    GREEN=''; YELLOW=''; CYAN=''; RED=''; NC=''
fi

info() { echo -e "${GREEN}[${AMBIENTE}]${NC} $*"; }
warn() { echo -e "${YELLOW}[${AMBIENTE}]${NC} $*"; }
err()  { echo -e "${RED}[${AMBIENTE}] ERRO:${NC} $*" >&2; exit 1; }
step() { echo -e "\n${CYAN}──── $* ────${NC}"; }

# ── 1. Verificar .env ─────────────────────────────────────────────────────────
step "1/4  Arquivo .env"

if [ ! -f "${ENV_FILE}" ]; then
    cp "${DIR}/.env.example" "${ENV_FILE}"
    echo ""
    echo -e "${YELLOW}┌──────────────────────────────────────────────────────────────┐${NC}"
    echo -e "${YELLOW}│  .env criado a partir de .env.example                        │${NC}"
    echo -e "${YELLOW}│                                                              │${NC}"
    echo -e "${YELLOW}│  Edite o arquivo antes de continuar:                         │${NC}"
    echo -e "${YELLOW}│    ${ENV_FILE}${NC}"
    echo -e "${YELLOW}│                                                              │${NC}"
    echo -e "${YELLOW}│  Valores obrigatórios:                                       │${NC}"
    echo -e "${YELLOW}│    APP_KEY          → php artisan key:generate --show        │${NC}"
    echo -e "${YELLOW}│    DB_PASSWORD      → senha do usuário laravel               │${NC}"
    echo -e "${YELLOW}│    DB_ROOT_PASSWORD → senha root do MariaDB                  │${NC}"
    echo -e "${YELLOW}└──────────────────────────────────────────────────────────────┘${NC}"
    echo ""
    exit 1
fi

info ".env encontrado."

# ── 2. Imagem da aplicação ────────────────────────────────────────────────────
step "2/4  Imagem da aplicação"

if docker compose -f "${COMPOSE}" pull app 2>/dev/null; then
    info "Imagem obtida via pull do GHCR."
else
    warn "Pull falhou (imagem não existe no GHCR ou sem credenciais)."
    info "Construindo imagem localmente — pode demorar alguns minutos..."
    docker compose -f "${COMPOSE}" build app
    info "Build concluído."
fi

# ── 3. Subir stack ────────────────────────────────────────────────────────────
step "3/4  Subindo stack ${AMBIENTE}"

docker compose -f "${COMPOSE}" up -d
info "Containers iniciados."

# ── 4. Aguardar app healthy ───────────────────────────────────────────────────
step "4/4  Aguardando healthcheck do app (até 90s)"

ELAPSED=0
until [ "$(docker inspect --format='{{.State.Health.Status}}' "${CONTAINER}" 2>/dev/null)" = "healthy" ]; do
    if [ "${ELAPSED}" -ge 90 ]; then
        echo ""
        err "App não ficou healthy em 90s. Últimas linhas do log:"$'\n'"$(docker logs "${CONTAINER}" --tail 40 2>&1)"
    fi
    printf "  aguardando... %ds\r" "${ELAPSED}"
    sleep 3
    ELAPSED=$(( ELAPSED + 3 ))
done

echo ""
echo -e "${GREEN}┌──────────────────────────────────────────────────────────────┐${NC}"
echo -e "${GREEN}│  Stack de produção pronta!                                   │${NC}"
echo -e "${GREEN}├──────────────────────────────────────────────────────────────┤${NC}"
echo -e "${GREEN}│  App     :  http://177.44.248.118:8091                       │${NC}"
echo -e "${GREEN}│  Healthz :  http://177.44.248.118:8091/healthz               │${NC}"
echo -e "${GREEN}│  Mailpit :  http://177.44.248.118:8025                       │${NC}"
echo -e "${GREEN}└──────────────────────────────────────────────────────────────┘${NC}"
echo ""
