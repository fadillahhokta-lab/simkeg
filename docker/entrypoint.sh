#!/bin/sh
set -e

cd /app

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY belum diisi. Buat dengan: php artisan key:generate --show" >&2
    exit 1
fi

# Volume storage bisa kosong pada pemasangan pertama di host tertentu; pastikan strukturnya ada.
mkdir -p storage/app/private storage/app/public storage/logs \
    storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing

# Migrasi otomatis: aman untuk satu instance. Kalau nanti ada banyak replika,
# pindahkan ke langkah deploy terpisah (lihat CLAUDE.md).
attempt=1
until php artisan migrate --force --no-interaction; do
    if [ "$attempt" -ge 30 ]; then
        echo "Migrasi gagal setelah $attempt percobaan." >&2
        exit 1
    fi
    echo "Database belum siap, mencoba lagi ($attempt)..." >&2
    attempt=$((attempt + 1))
    sleep 2
done

# Role, Admin pertama (ADMIN_USERNAME/ADMIN_PASSWORD), dan jenis kegiatan awal. Idempoten.
php artisan db:seed --force --no-interaction

php artisan optimize

exec docker-php-entrypoint "$@"
