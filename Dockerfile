# syntax=docker/dockerfile:1

# ---- Base: FrankenPHP + ekstensi PHP yang dibutuhkan aplikasi ----
FROM dunglas/frankenphp:1-php8.3 AS base

# Ekstensi baru ditambahkan di sini (lihat CLAUDE.md).
# pdo_mysql: MariaDB · zip, gd: maatwebsite/excel & dompdf · intl: format angka/tanggal
RUN install-php-extensions pdo_mysql zip gd intl \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-simkeg.ini"

WORKDIR /app

# ---- Dependency PHP (tanpa dev) ----
FROM base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction

# ---- Aset front-end (Vite + Tailwind) ----
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
# app.js mengimpor Livewire dari vendor, dan Tailwind memindai view paginasi Laravel.
COPY --from=vendor /app/vendor/livewire ./vendor/livewire
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources ./vendor/laravel/framework/src/Illuminate/Pagination/resources
RUN npm run build

# ---- Image akhir ----
FROM base AS app

ARG USER=simkeg
ARG UID=1000

# Port 80 tanpa root: beri kapabilitas bind ke binary FrankenPHP.
RUN useradd --uid "$UID" --create-home "$USER" \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R "$USER:$USER" /data/caddy /config/caddy

COPY --chown=$USER:$USER --from=vendor /app /app
COPY --chown=$USER:$USER --from=assets /app/public/build /app/public/build
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/simkeg-entrypoint

USER $USER

ENV SERVER_NAME=:80 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

EXPOSE 80
VOLUME ["/app/storage"]

ENTRYPOINT ["simkeg-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
