#!/usr/bin/env bash
# provision.sh — bootstrap idempotente da VM Univates
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_URL="https://github.com/rafaelbrutscher/registros-financeiros.git"
APP_DIR="/opt/app/registros-financeiros"

# ── Cores ANSI (desabilitadas quando stdout não é terminal) ───────────────────
if [ -t 1 ]; then
    GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
    RED='\033[0;31m'; NC='\033[0m'
else
    GREEN=''; YELLOW=''; CYAN=''; RED=''; NC=''
fi

info() { echo -e "${GREEN}[provision]${NC} $*"; }
warn() { echo -e "${YELLOW}[provision]${NC} $*"; }
err()  { echo -e "${RED}[provision] ERRO:${NC} $*" >&2; exit 1; }
step() { echo -e "\n${CYAN}──── $* ────${NC}"; }

# ── Detectar se está rodando como root ────────────────────────────────────────
if [ "$(id -u)" -eq 0 ]; then
    IS_ROOT=true
else
    IS_ROOT=false
fi

# ── 1. Docker ─────────────────────────────────────────────────────────────────
step "1/6  Docker"

if command -v docker > /dev/null 2>&1; then
    DOCKER_INFO="$(docker info 2>&1 || true)"

    if echo "${DOCKER_INFO}" | grep -qi "permission denied"; then
        echo ""
        warn "Docker está instalado mas '${USER}' não tem permissão de acesso."
        warn "Você precisa fazer logout e login para que o grupo 'docker' seja aplicado."
        warn ""
        warn "Execute:  exit"
        warn "Faça login novamente e rode:  bash infra/provision.sh"
        echo ""
        exit 1
    elif ! echo "${DOCKER_INFO}" | grep -q "Server Version"; then
        info "Daemon não responde. Tentando iniciar..."
        sudo systemctl start docker
        docker info > /dev/null 2>&1 || err "Daemon não responde após start. Verifique: sudo systemctl status docker"
    fi

    info "Docker OK — $(docker --version)"
else
    info "Docker não encontrado. Instalando via script oficial..."
    curl -fsSL https://get.docker.com | sudo sh
    sudo usermod -aG docker univates

    if [ "${IS_ROOT}" = true ]; then
        info "Habilitando e iniciando o serviço do Docker..."
        systemctl enable --now docker || err "Falha ao iniciar o serviço do Docker. Verifique: systemctl status docker"

        docker info > /dev/null 2>&1 || err "Docker instalado mas o daemon não responde. Verifique: systemctl status docker"
        info "Docker OK — $(docker --version)"
    else
        echo ""
        echo -e "${YELLOW}│  Docker instalado com sucesso.                               │${NC}"
        echo ""
        exit 0
    fi
fi

# ── 2. Plugin compose ─────────────────────────────────────────────────────────
step "2/6  Plugin docker compose"

if docker compose version > /dev/null 2>&1; then
    info "Plugin compose OK — $(docker compose version)"
else
    info "Instalando docker-compose-plugin via apt..."
    sudo apt-get update -qq
    sudo apt-get install -y docker-compose-plugin
    info "Plugin compose instalado."
fi

# ── 3. Diretório de trabalho ──────────────────────────────────────────────────
step "3/6  Diretório /opt/app"

if [ "${IS_ROOT}" = true ]; then
    mkdir -p /opt/app
    chown -R univates:univates /opt/app
else
    [ -d /opt/app ] || err "/opt/app não existe. Execute provision.sh como root na primeira instalação."
fi
info "/opt/app pronto."

# ── 4. Repositório ────────────────────────────────────────────────────────────
step "4/6  Repositório"

if [ -d "${APP_DIR}/.git" ]; then
    info "Repositório já clonado em ${APP_DIR}. Atualizando..."
    if [ "${IS_ROOT}" = true ]; then
        chown -R univates:univates "${APP_DIR}"
        sudo -u univates git -C "${APP_DIR}" pull --ff-only
    else
        git -C "${APP_DIR}" pull --ff-only
    fi
else
    info "Clonando repositório em ${APP_DIR}..."
    if [ "${IS_ROOT}" = true ]; then
        sudo -u univates git clone "${REPO_URL}" "${APP_DIR}"
    else
        git clone "${REPO_URL}" "${APP_DIR}"
    fi
fi

# ── 5. Redes Docker ───────────────────────────────────────────────────────────
step "5/6  Redes Docker"

bash "${REPO_DIR}/infra/networks.sh"

# ── 6. Mailpit ────────────────────────────────────────────────────────────────
step "6/6  Mailpit"

docker compose -f "${REPO_DIR}/infra/mailpit/docker-compose.yml" up -d

info "Aguardando Mailpit ficar healthy (até 60s)..."
ELAPSED=0
until [ "$(docker inspect --format='{{.State.Health.Status}}' mailpit 2>/dev/null)" = "healthy" ]; do
    if [ "${ELAPSED}" -ge 60 ]; then
        err "Mailpit não ficou healthy em 60s. Verifique: docker logs mailpit"
    fi
    sleep 2
    ELAPSED=$(( ELAPSED + 2 ))
done
info "Mailpit healthy."

# ── Banner final ──────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}│  Mailpit UI  :  http://177.44.248.118:8025                   │${NC}"
echo -e "${GREEN}│  Homolog     :  http://177.44.248.118:8090                   │${NC}"
echo -e "${GREEN}│  Prod        :  http://177.44.248.118:8091                   │${NC}"
echo ""
