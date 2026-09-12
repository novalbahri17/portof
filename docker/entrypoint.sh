#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# ---- Database ----
# MySQL berada di luar Dokploy (server eksternal, nyala 24 jam),
# jadi tidak perlu ditunggu. Langsung lanjut ke cache & migrasi.

# ---- Ensure storage structure ----
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---- Generate key if missing ----
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# ---- Cache config / routes / views ----
echo "📦 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---- Run migrations ----
# Sengaja tidak pakai `set -e` di sini: kalau MySQL sempat tidak terjangkau,
# kita tetap mau nginx/php-fpm nyala dan log errornya kelihatan,
# bukan container mati diam-diam.
echo "🗄️  Running migrations..."
if ! php artisan migrate --force; then
    echo "❌ Migrasi gagal — cek kredensial DB_HOST/DB_USERNAME/DB_PASSWORD dan whitelist IP."
fi

# ---- Storage link ----
php artisan storage:link --force 2>/dev/null || true

# ---- Seed admin & data contoh on first run ----
# Cek jumlah user: kalau belum ada, artinya belum pernah di-seed.
SEEDED=$(php artisan tinker --execute="echo \App\Models\User::count() > 0 ? 'yes' : 'no';" 2>/dev/null | tail -1)
if [ "$SEEDED" != "yes" ]; then
    echo "🌱 Database kosong — menjalankan seeder..."
    php artisan db:seed --class=AdminSeeder --force || echo "⚠️  AdminSeeder gagal"
    php artisan db:seed --class=NouvalProfileSeeder --force || echo "⚠️  NouvalProfileSeeder gagal"
else
    echo "✅ Database sudah terisi, lewati seeding"
fi

# ---- Generate Wayfinder routes ----
echo "🧭 Generating Wayfinder routes..."
php artisan wayfinder:generate 2>/dev/null || true

echo "✅ Application ready — starting services"

# ---- Start Supervisor (nginx + php-fpm + queue) ----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
