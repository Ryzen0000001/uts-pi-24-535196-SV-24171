#!/bin/sh
set -eu

# Tunggu basis data siap, lalu jalankan migrasi. Percobaan diulang karena
# container MySQL bisa saja belum menerima koneksi saat backend mulai.
attempt=1
until php artisan migrate --force --no-interaction; do
    if [ "$attempt" -ge 30 ]; then
        echo "Migrasi gagal setelah $attempt percobaan." >&2
        exit 1
    fi
    echo "Basis data belum siap (percobaan $attempt/30), menunggu 2 detik..." >&2
    attempt=$((attempt + 1))
    sleep 2
done

# Akun demo dibuat lewat seeder karena registrasi publik ditutup.
php artisan db:seed --class=DemoSeeder --force --no-interaction

php artisan config:cache
php artisan route:cache

exec "$@"
