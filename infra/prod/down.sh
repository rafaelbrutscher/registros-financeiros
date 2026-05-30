#!/usr/bin/env bash
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE="${DIR}/docker-compose.yml"

docker compose -f "${COMPOSE}" down

echo ""
echo "Stack de produção parada. Mailpit e homolog permanecem de pé."
echo ""
