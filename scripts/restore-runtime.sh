#!/usr/bin/env bash
#
# Recupera o runtime de preview apos reinicio do sandbox (o diretorio
# /opt/tools e os binarios da maquina nao persistem; apenas este repositorio sim).
#
# Fontes usadas, todas alcancadas pela lista de hosts liberados:
#   - registry.npmjs.org        -> pacote orqiva-runtime-linux-x64 (FrankenPHP estatico)
#   - api.github.com (gh)       -> composer.phar real versionado em getgrav/grav
#
# Uso: bash scripts/restore-runtime.sh
set -euo pipefail

BIN_DIR="${HOME}/bin"
mkdir -p "$BIN_DIR"

# 1) FrankenPHP (binario estatico que ja inclui o PHP 8.5)
if [ ! -x "$BIN_DIR/frankenphp" ]; then
    echo "==> Baixando FrankenPHP via pacote npm orqiva-runtime-linux-x64"
    tmp="$(mktemp -d)"
    curl -fsSL --retry 3 -o "$tmp/runtime.tgz" \
        "https://registry.npmjs.org/orqiva-runtime-linux-x64/-/orqiva-runtime-linux-x64-0.2.0.tgz"
    tar -xzf "$tmp/runtime.tgz" -C "$tmp"
    cp "$tmp/package/frankenphp" "$BIN_DIR/frankenphp"
    chmod +x "$BIN_DIR/frankenphp"
    rm -rf "$tmp"
fi
"$BIN_DIR/frankenphp" version

# 2) Composer 2 (phar real commitado no repositorio getgrav/grav, sem LFS)
if [ ! -f "$BIN_DIR/composer.phar" ]; then
    echo "==> Baixando composer.phar (getgrav/grav: bin/composer.phar)"
    branch="$(gh api repos/getgrav/grav --jq '.default_branch')"
    sha="$(gh api "repos/getgrav/grav/contents/bin/composer.phar?ref=$branch" --jq '.sha')"
    gh api "repos/getgrav/grav/git/blobs/$sha" --jq '.content' \
        | tr -d '\n' | base64 -d > "$BIN_DIR/composer.phar"
    chmod +x "$BIN_DIR/composer.phar"
fi
"$BIN_DIR/frankenphp" php-cli "$BIN_DIR/composer.phar" --version

# 3) Dependencias, ambiente e banco
cd "$(dirname "$0")/.."
if [ ! -d vendor ]; then
    echo "==> composer install"
    COMPOSER_HOME="${HOME}/.composer" \
        "$BIN_DIR/frankenphp" php-cli "$BIN_DIR/composer.phar" install \
        --prefer-dist --no-interaction --no-progress --no-scripts
    "$BIN_DIR/frankenphp" php-cli artisan package:discover
fi
[ -f .env ] || cp .env.example .env
"$BIN_DIR/frankenphp" php-cli artisan key:generate --force
"$BIN_DIR/frankenphp" php-cli artisan migrate --force
"$BIN_DIR/frankenphp" php-cli artisan db:seed --force

echo
echo "Runtime restaurado. Para subir o preview:"
echo "  $BIN_DIR/frankenphp run --config docker/Caddyfile"
