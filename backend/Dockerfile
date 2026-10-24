# syntax=docker/dockerfile:1

# ========== STAGE 1: DEPENDENSI ==========
# Versi FrankenPHP dan PHP dikunci agar build dapat diulang.
FROM dunglas/frankenphp:1.13.3-php8.4-alpine AS vendor

RUN install-php-extensions pdo_mysql intl bcmath opcache

COPY --from=composer:2.10.3 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# composer.json dan composer.lock disalin lebih dulu supaya layer dependensi
# tetap ter-cache selama kode aplikasi belum berubah.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --prefer-dist \
        --no-progress

COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ========== STAGE 2: RUNTIME (FRANKENPHP) ==========
FROM dunglas/frankenphp:1.13.3-php8.4-alpine AS runtime

RUN install-php-extensions pdo_mysql intl bcmath opcache \
    && addgroup -S app \
    && adduser -S -G app -h /app app

WORKDIR /app

COPY --from=vendor --chown=app:app /app /app

# Konfigurasi Caddy: FrankenPHP melayani public/ Laravel dan menulis access log
# ke stdout agar terbaca lewat `docker compose logs backend`.
COPY --chown=app:app docker/Caddyfile /etc/caddy/Caddyfile
COPY --chown=app:app docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R app:app storage bootstrap/cache

# FrankenPHP/Caddy menyimpan state di direktori ini; arahkan ke folder yang
# dapat ditulis user non-root.
ENV XDG_CONFIG_HOME=/app/storage/frankenphp/config \
    XDG_DATA_HOME=/app/storage/frankenphp/data

RUN mkdir -p "$XDG_CONFIG_HOME" "$XDG_DATA_HOME" \
    && chown -R app:app storage/frankenphp

USER app

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1:8000/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
