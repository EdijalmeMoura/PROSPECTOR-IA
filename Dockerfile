# syntax=docker/dockerfile:1
FROM dunglas/frankenphp:1-php8.3-bookworm

RUN install-php-extensions pdo_mysql pdo_sqlite mbstring intl zip bcmath pcntl opcache
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && chmod +x docker/entrypoint.sh
COPY docker/Caddyfile.render /etc/caddy/Caddyfile
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr PORT=10000
EXPOSE 10000
ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
