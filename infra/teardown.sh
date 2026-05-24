#!/usr/bin/env bash
# teardown.sh — para todos os containers do projeto, remove volumes e redes
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [ -t 1 ]; then
    GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
else
    GREEN=''; YELLOW=''; NC=''
fi

info() { echo -e "${GREEN}[teardown]${NC} $*"; }
warn() { echo -e "${YELLOW}[teardown]${NC} $*"; }
step() { echo -e "\n${YELLOW}──── $* ────${NC}"; }

# ── 1. Containers ─────────────────────────────────────────────────────────────
step "1/3  Parando containers"

for compose_file in \
    "${REPO_DIR}/infra/mailpit/docker-compose.yml" \
    "${REPO_DIR}/infra/homolog/docker-compose.yml" \
    "${REPO_DIR}/infra/prod/docker-compose.yml"
do
    if [ -f "${compose_file}" ]; then
        stack="$(basename "$(dirname "${compose_file}")")"
        info "Parando stack: ${stack}"
        docker compose -f "${compose_file}" down --remove-orphans 2>/dev/null || true
    else
        warn "Compose não encontrado, pulando: ${compose_file}"
    fi
done

# ── 2. Volumes ────────────────────────────────────────────────────────────────
step "2/3  Removendo volumes"

for vol in mailpit_data mariadb_homolog_data mariadb_prod_data; do
    if docker volume inspect "${vol}" > /dev/null 2>&1; then
        docker volume rm "${vol}"
        info "Volume '${vol}' removido."
    else
        warn "Volume '${vol}' não existe, pulando."
    fi
done

# ── 3. Redes ──────────────────────────────────────────────────────────────────
step "3/3  Removendo redes"

for net in homolog_net prod_net shared_net; do
    if docker network inspect "${net}" > /dev/null 2>&1; then
        docker network rm "${net}"
        info "Rede '${net}' removida."
    else
        warn "Rede '${net}' não existe, pulando."
    fi
done

# ── Banner ────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}┌──────────────────────────────────────────────────────────────┐${NC}"
echo -e "${GREEN}│  Teardown completo. Estado limpo para apresentação.          │${NC}"
echo -e "${GREEN}│                                                              │${NC}"
echo -e "${GREEN}│  Para recriar tudo:  bash infra/provision.sh                 │${NC}"
echo -e "${GREEN}└──────────────────────────────────────────────────────────────┘${NC}"
echo ""
