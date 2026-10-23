#!/bin/sh
set -eu
cd /app
if [ -z "${APP_KEY:-}" ]; then
    echo 'ERRO: configure APP_KEY no Render (base64: seguido de 32 bytes aleatorios em base64).' >&2
    exit 1
fi
# Nao gerar nova APP_KEY a cada deploy: isso invalidaria cookies e dados cifrados.
php artisan config:clear
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi
# Nao executar seeders automaticamente: nao redefinir senha/admin nem inserir demos.
php artisan view:cache
exec "$@"
