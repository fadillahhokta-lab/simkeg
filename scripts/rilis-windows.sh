#!/usr/bin/env bash
# Membuat ZIP rilis SIMKEG untuk Windows + Laragon.
# Isi: kode aplikasi, vendor (tanpa paket dev), dan aset Vite yang sudah di-build,
# sehingga di komputer klien tidak perlu Composer maupun Node.js.
#
# Pemakaian: scripts/rilis-windows.sh [versi]   (default: APP_VERSION di .env.example)
#            DARI_WORKTREE=1 scripts/rilis-windows.sh   (uji coba dari folder kerja, sebelum commit)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VERSI="${1:-$(grep -E '^APP_VERSION=' "$ROOT/.env.example" | cut -d= -f2)}"
NAMA="simkeg-${VERSI}-windows"
KERJA="$(mktemp -d)"
TUJUAN="$KERJA/simkeg"
trap 'rm -rf "$KERJA"' EXIT

cd "$ROOT"
mkdir -p "$TUJUAN"
if [ "${DARI_WORKTREE:-0}" = "1" ]; then
    echo "1/4 Menyalin kode dari folder kerja (uji coba)..."
    git ls-files -co --exclude-standard | rsync -a --files-from=- . "$TUJUAN/"
else
    if [ -n "$(git status --porcelain)" ]; then
        echo "Ada perubahan yang belum di-commit. ZIP rilis dibuat dari commit terakhir (HEAD)." >&2
    fi
    echo "1/4 Menyalin kode dari HEAD..."
    git archive HEAD | tar -x -C "$TUJUAN"
fi

echo "2/4 Memasang dependency PHP (tanpa dev)..."
composer install --working-dir="$TUJUAN" --no-dev --prefer-dist --optimize-autoloader \
    --no-interaction --no-progress --quiet

echo "3/4 Build aset front-end..."
(cd "$TUJUAN" && npm ci --no-audit --no-fund --silent && npm run build --silent >/dev/null)

echo "4/4 Merapikan dan membuat ZIP..."
(
    cd "$TUJUAN"
    # Hanya dibutuhkan untuk development atau Docker.
    rm -rf node_modules tests docker scripts Dockerfile .dockerignore compose.yaml \
        phpunit.xml AGENTS.md .editorconfig .npmrc
    # Cache paket dari mesin build harus dibuat ulang di Windows.
    rm -f bootstrap/cache/*.php
)
mkdir -p "$ROOT/dist"
rm -f "$ROOT/dist/$NAMA.zip"
(cd "$KERJA" && zip -qr "$ROOT/dist/$NAMA.zip" simkeg)

echo "Selesai: dist/$NAMA.zip ($(du -h "$ROOT/dist/$NAMA.zip" | cut -f1))"
