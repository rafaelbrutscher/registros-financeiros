#!/usr/bin/env bash
# networks.sh — cria as redes Docker do projeto se não existirem
set -euo pipefail

create_network() {
    local name="$1"
    if docker network inspect "${name}" > /dev/null 2>&1; then
        echo "[networks] Rede '${name}' já existe."
    else
        docker network create "${name}"
        echo "[networks] Rede '${name}' criada."
    fi
}

create_network homolog_net
create_network prod_net
create_network shared_net
