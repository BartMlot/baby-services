#!/bin/sh
set -e

echo "[notification] Clearing cache..."
php bin/console cache:clear --no-warmup

echo "[notification] Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "[notification] Starting AMQP consumer in background (auto-restart on failure)..."
(while true; do
    php bin/console messenger:consume async --time-limit=3600 -vv
    echo "[notification] Consumer exited, restarting in 3s..."
    sleep 3
done) &

echo "[notification] Starting PHP server on :8000..."
exec php -S 0.0.0.0:8000 -t public/
