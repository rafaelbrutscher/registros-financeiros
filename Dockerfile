# ── Stage 1: Vendor de produção (sem dev deps) ────────────────────────────────
FROM composer:2 AS vendor-prod
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-scripts \
        --no-interaction \
        --prefer-dist

# ── Stage 2: Vendor de desenvolvimento (inclui PHPUnit, Faker etc.) ───────────
FROM composer:2 AS vendor-dev
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --optimize-autoloader \
        --no-scripts \
        --no-interaction \
        --prefer-dist

# ── Stage 3: Assets frontend (Vite + Tailwind) ────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json vite.config.js ./
COPY resources/ ./resources/
RUN npm install && npm run build

# ── Stage 4: Runtime — imagem de produção ────────────────────────────────────
FROM php:8.3-fpm-alpine AS runtime

ARG APP_UID=1001
ARG APP_GID=1001

# Dependências de sistema + extensões PHP
RUN apk add --no-cache \
        nginx \
        supervisor \
        su-exec \
        libzip-dev \
        icu-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        zip \
        intl \
        bcmath \
        opcache \
    && rm -rf /tmp/pear

# Usuário não-root com UID/GID configuráveis via build arg
RUN addgroup -S -g "${APP_GID}" app \
    && adduser  -S -u "${APP_UID}" -G app app

WORKDIR /var/www/html

# Código-fonte da aplicação
COPY --chown=app:app . .

# Sobrepõe vendor (prod) e assets compilados
COPY --from=vendor-prod --chown=app:app /app/vendor       ./vendor
COPY --from=assets      --chown=app:app /app/public/build ./public/build

# Arquivos de configuração Docker
COPY docker/nginx.conf       /etc/nginx/nginx.conf
COPY docker/php.ini          "$PHP_INI_DIR/conf.d/app.ini"
COPY docker/php-fpm.conf     /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh    /entrypoint.sh

# Remove override padrão do FPM; prepara diretórios e permissões
RUN rm -f /usr/local/etc/php-fpm.d/zz-docker.conf \
    && mkdir -p \
        storage/framework/{views,sessions,cache} \
        storage/logs \
        bootstrap/cache \
        /run/nginx \
        /var/log/nginx \
        /var/log/supervisor \
    && chown -R app:app storage bootstrap/cache \
    && chmod -R 775    storage bootstrap/cache \
    && chmod +x /entrypoint.sh

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD wget -qO- http://localhost:8080/healthz || exit 1

ENTRYPOINT ["/entrypoint.sh"]

# ── Stage 5: CI — herda runtime, adiciona vendor-dev + PCOV ──────────────────
FROM runtime AS ci

# Substitui vendor pelo conjunto completo (dev deps incluídos)
COPY --from=vendor-dev --chown=app:app /app/vendor ./vendor

# Instala PCOV apenas neste stage — não entra em produção
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del .build-deps
