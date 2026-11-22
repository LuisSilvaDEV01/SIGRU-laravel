#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[sigru] Aguardando MySQL em ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
attempts=0
until php -r '
$host = getenv("DB_HOST") ?: "mysql";
$port = (int) (getenv("DB_PORT") ?: 3306);
$user = getenv("DB_USERNAME") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "";
$db   = getenv("DB_DATABASE") ?: "sigru";
try {
    new PDO(
        sprintf("mysql:host=%s;port=%d;dbname=%s", $host, $port, $db),
        $user,
        $pass,
        [PDO::ATTR_TIMEOUT => 2]
    );
    exit(0);
} catch (Throwable $e) {
    exit(1);
}
'; do
  attempts=$((attempts + 1))
  if [ "$attempts" -ge 60 ]; then
    echo "[sigru] MySQL não respondeu a tempo."
    exit 1
  fi
  sleep 2
done
echo "[sigru] MySQL OK."

if [ ! -f vendor/autoload.php ]; then
  echo "[sigru] Instalando dependências Composer..."
  composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
  echo "[sigru] Criando .env a partir de .env.example..."
  cp .env.example .env
fi

# Não sobrescreve o .env do host: no Docker as variáveis do compose
# (DB_HOST=mysql etc.) têm prioridade sobre o arquivo.
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "[sigru] Gerando APP_KEY..."
  php artisan key:generate --force --no-interaction
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

echo "[sigru] Subindo Laravel em http://0.0.0.0:8000"
exec "$@"
