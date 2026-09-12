#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# ---- Database ----
# MySQL berada di luar Dokploy (server eksternal, nyala 24 jam),
# jadi tidak perlu ditunggu. Langsung lanjut ke cache & migrasi.

# ---- Ensure storage structure ----
mkdir -p \
    storage/app/private \
    storage/app/public/blogs \
    storage/app/public/certifications/images \
    storage/app/public/certifications/pdfs \
    storage/app/public/logo \
    storage/app/public/projects \
    storage/app/public/seo \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Folder kosong tidak ikut tersimpan di image/volume, jadi pastikan
# setiap folder unggahan punya penanda supaya tidak pernah hilang.
for dir in \
    storage/app/private \
    storage/app/public/blogs \
    storage/app/public/certifications/images \
    storage/app/public/certifications/pdfs \
    storage/app/public/logo \
    storage/app/public/projects \
    storage/app/public/seo; do
    if [ ! -f "$dir/.gitkeep" ]; then
        : > "$dir/.gitkeep"
    fi
done

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---- Generate key if missing ----
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# ---- Storage link ----
# Dijalankan lebih awal: tidak butuh database, dan dibutuhkan agar
# gambar yang diunggah bisa diakses begitu aplikasi hidup.
php artisan storage:link --force 2>/dev/null || true

# ---- Run migrations ----
# PENTING: migrasi HARUS jalan sebelum config/route/view cache.
# SESSION_DRIVER=database & CACHE_STORE=database, jadi tabel sessions/cache
# harus ada dulu. Kalau cache dibuat lebih dulu lalu tabel belum ada,
# request pertama akan error 500 dan situs tampak "tidak berubah".
# Sengaja tidak pakai `set -e` di sini: kalau MySQL sempat tidak terjangkau,
# kita tetap mau nginx/php-fpm nyala dan log errornya kelihatan,
# bukan container mati diam-diam.
echo "🗄️  Running migrations..."
if ! php artisan migrate --force; then
    echo "❌ Migrasi gagal — cek kredensial DB_HOST/DB_USERNAME/DB_PASSWORD dan whitelist IP."
fi

# ---- Cache config / routes / views ----
# Dijalankan setelah migrasi selesai, supaya konfigurasi yang di-cache
# benar-benar cocok dengan skema database yang sudah ter-migrate.
echo "📦 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---- Seed admin on first run ----
# PENTING: hanya AdminSeeder yang boleh jalan otomatis.
# NouvalProfileSeeder berisi data contoh dan MENIMPA data asli
# (termasuk gambar & sertifikat yang sudah diunggah), jadi hanya
# dijalankan kalau memang diminta lewat SEED_DATA=1.
SEEDED=$(php artisan tinker --execute="echo \App\Models\User::count() > 0 ? 'yes' : 'no';" 2>/dev/null | tail -1)
if [ "$SEEDED" != "yes" ]; then
    echo "🌱 Belum ada user — membuat akun admin..."
    php artisan db:seed --class=AdminSeeder --force || echo "⚠️  AdminSeeder gagal"
else
    echo "✅ Database sudah terisi, lewati seeding"
fi

if [ "$SEED_DATA" = "1" ]; then
    echo "⚠️  SEED_DATA=1 — menjalankan NouvalProfileSeeder (data contoh akan menimpa data yang ada)..."
    php artisan db:seed --class=NouvalProfileSeeder --force || echo "⚠️  NouvalProfileSeeder gagal"
fi

# ---- Generate Wayfinder routes ----
echo "🧭 Generating Wayfinder routes..."
php artisan wayfinder:generate 2>/dev/null || true

# ---- Tampilkan stempel build di log ----
# Berguna untuk memastikan container yang jalan memang image hasil build terbaru.
if [ -f build-info.json ]; then
    echo "🏷️  Build info: $(cat build-info.json | tr -d '\n')"
else
    echo "🏷️  Build info: tidak ada (image dibuild tanpa stempel)"
fi

echo "✅ Application ready — starting services"

# ---- Start Supervisor (nginx + php-fpm + queue) ----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
