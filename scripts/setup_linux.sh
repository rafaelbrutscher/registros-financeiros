#!/usr/bin/env bash
set -euo pipefail

# Configuracao padrao (pode sobrescrever via variaveis de ambiente)
APP_NAME="${APP_NAME:-registro-despesas-receitas}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-registro_financeiro}"
DB_USERNAME="${DB_USERNAME:-registro_user}"
DB_PASSWORD="${DB_PASSWORD:-registro_pass}"

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ "${EUID}" -ne 0 ]]; then
	echo "Execute como root: sudo bash scripts/setup_linux.sh"
	exit 1
fi

command -v apt >/dev/null 2>&1 || {
	echo "Este script foi preparado para distribuicoes baseadas em Debian/Ubuntu (apt)."
	exit 1
}

set_env_value() {
	local key="$1"
	local value="$2"
	local file="$3"

	if grep -q "^${key}=" "${file}"; then
		sed -i "s|^${key}=.*|${key}=${value}|" "${file}"
	else
		echo "${key}=${value}" >> "${file}"
	fi
}

echo "[1/9] Instalando pacotes do sistema..."
apt update
apt install -y \
	ca-certificates \
	curl \
	git \
	unzip \
	nginx \
	mysql-server \
	php-cli \
	php-fpm \
	php-mysql \
	php-mbstring \
	php-xml \
	php-curl \
	php-zip \
	php-bcmath \
	php-intl \
	composer \
	nodejs \
	npm

echo "[2/9] Habilitando servicos..."
systemctl enable --now mysql
systemctl enable --now nginx

echo "[3/9] Configurando banco de dados MySQL..."
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[4/9] Preparando .env..."
cd "${PROJECT_DIR}"

if [[ ! -f .env ]]; then
	cp .env.example .env
fi

set_env_value "APP_NAME" "\"${APP_NAME}\"" .env
set_env_value "APP_ENV" "${APP_ENV}" .env
set_env_value "APP_DEBUG" "${APP_DEBUG}" .env
set_env_value "APP_URL" "${APP_URL}" .env
set_env_value "DB_CONNECTION" "mysql" .env
set_env_value "DB_HOST" "${DB_HOST}" .env
set_env_value "DB_PORT" "${DB_PORT}" .env
set_env_value "DB_DATABASE" "${DB_DATABASE}" .env
set_env_value "DB_USERNAME" "${DB_USERNAME}" .env
set_env_value "DB_PASSWORD" "${DB_PASSWORD}" .env

echo "[5/9] Instalando dependencias do projeto..."
composer install --no-interaction --prefer-dist --optimize-autoloader

if [[ -f package.json ]]; then
	npm install
	npm run build
fi

echo "[6/9] Configurando Laravel..."
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true

echo "[7/9] Ajustando permissoes..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "[8/9] Configurando Nginx..."
PHP_FPM_SOCK="$(find /run/php -maxdepth 1 -name 'php*-fpm.sock' | head -n 1)"
if [[ -z "${PHP_FPM_SOCK}" ]]; then
	echo "Nao foi possivel encontrar o socket do PHP-FPM em /run/php"
	exit 1
fi

PHP_FPM_SERVICE="$(basename "${PHP_FPM_SOCK}" .sock)"
systemctl enable --now "${PHP_FPM_SERVICE}"

cat > /etc/nginx/sites-available/${APP_NAME} <<EOF
server {
	listen 80;
	server_name _;
	root ${PROJECT_DIR}/public;
	index index.php index.html;

	add_header X-Frame-Options "SAMEORIGIN";
	add_header X-Content-Type-Options "nosniff";

	charset utf-8;

	location / {
		try_files \$uri \$uri/ /index.php?\$query_string;
	}

	location = /favicon.ico { access_log off; log_not_found off; }
	location = /robots.txt  { access_log off; log_not_found off; }

	error_page 404 /index.php;

	location ~ \.php$ {
		fastcgi_pass unix:${PHP_FPM_SOCK};
		fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
		include fastcgi_params;
	}

	location ~ /\.(?!well-known).* {
		deny all;
	}
}
EOF

ln -sf /etc/nginx/sites-available/${APP_NAME} /etc/nginx/sites-enabled/${APP_NAME}
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl restart nginx

echo "[9/9] Finalizado."
IP_ADDRESS="$(hostname -I | awk '{print $1}')"
echo "Aplicacao publicada em: http://${IP_ADDRESS}/lancamentos"
echo "Login padrao: admin"
echo "Senha padrao: 123456"
