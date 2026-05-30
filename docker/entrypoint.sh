#!/bin/sh
set -e

# ── 1. Permissões (necessário para bind mounts em dev) ────────────────────────
chown -R app:app /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775     /var/www/html/storage /var/www/html/bootstrap/cache

# ── 2. APP_KEY de fallback (evita erro de boot em ambientes sem .env) ─────────
if [ -z "${APP_KEY:-}" ]; then
    echo "[entrypoint] APP_KEY ausente — gerando chave efêmera para este container"
    GENERATED_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    export APP_KEY="$GENERATED_KEY"
fi

# ── 3. Descoberta de pacotes Composer (build usou --no-scripts) ───────────────
su-exec app php artisan package:discover --ansi 2>/dev/null || true

# ── 4. Limpa caches antigos (evita config baked com env errado do host) ───────
su-exec app php artisan config:clear 2>/dev/null || true
su-exec app php artisan route:clear  2>/dev/null || true
su-exec app php artisan view:clear   2>/dev/null || true

# ── 5. Migrations opcionais (defina RUN_MIGRATIONS=true no compose) ──────────
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    su-exec app php artisan migrate --force
    su-exec app php artisan db:seed --force
fi

# ── 6. Cache de configuração apenas em produção/staging ──────────────────────
if [ "${APP_ENV}" = "production" ] || [ "${APP_ENV}" = "staging" ]; then
    su-exec app php artisan config:cache
    su-exec app php artisan route:cache
    su-exec app php artisan view:cache
fi

# ── 7. Inicia supervisord (nginx + php-fpm) ───────────────────────────────────
exec /usr/bin/supervisord -c /etc/supervisord.conf
