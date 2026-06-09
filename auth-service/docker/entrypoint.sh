#!/bin/sh
set -e

if [ ! -f /app/config/jwt/private.pem ]; then
    echo "[auth] Generating JWT keys..."
    mkdir -p /app/config/jwt
    openssl genpkey -out /app/config/jwt/private.pem -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in /app/config/jwt/private.pem -out /app/config/jwt/public.pem -pubout
    echo "[auth] JWT keys generated."
fi

echo "[auth] Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "[auth] Starting PHP server on :8000..."
exec php -S 0.0.0.0:8000 -t public/
