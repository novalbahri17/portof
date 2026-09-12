#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# ---- Wait for database ----
# Mendukung MySQL/MariaDB. Untuk driver lain, tunggu via TCP sederhana.
DB_WAIT_TIMEOUT="${DB_WAIT_TIMEOUT:-60}"
waited=0

if [ "${DB_CONNECTION:-mysql}" = "mysql" ] || [ "${DB_CONNECTION:-mysql}" = "mariadb" ]; then
    echo "⏳ Waiting for MySQL at ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}..."
    until mysqladmin ping \
            -h "${DB_HOST:-127.0.0.1}" \
            -P "${DB_PORT:-3306}" \
            -u "${DB_USERNAME:-root}" \
            ${DB_PASSWORD:+-p"${DB_PASSWORD}"} \
            --silent --connect-timeout=3 >/dev/null 2>&1; do
        waited=$((waited + 2))
        if [ "$waited" -ge "$DB_WAIT_TIMEOUT" ]; then
            echo "⚠️  MySQL tidak merespons setelah ${DB_WAIT_TIMEOUT}s — lanjut saja (migrasi akan mencoba lagi)"
            break
        fi
        sleep 2
    done
else
    echo "⏳ Waiting for database at ${DB_HOST:-127.0.0.1}:${DB_PORT:-5432}..."
    until php -r "exit(@fsockopen(getenv('DB_HOST') ?: '127.0.0.1', (int)(getenv('DB_PORT') ?: 5432)) ? 0 : 1);" 2>/dev/null; do
        waited=$((waited + 2))
        if [ "$waited" -ge "$DB_WAIT_TIMEOUT" ]; then
            echo "⚠️  Database tidak merespons setelah ${DB_WAIT_TIMEOUT}s — lanjut saja"
            break
        fi
        sleep 2
    done
fi
echo "✅ Database siap"

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
echo "🗄️  Running migrations..."
php artisan migrate --force

# ---- Storage link ----
php artisan storage:link --force 2>/dev/null || true

# ---- Seed admin on first run ----
# Check if admin user exists as indicator that seeding was already done
SEEDED=$(php artisan tinker --execute="echo \App\Models\User::where('is_admin', true)->exists() ? 'yes' : 'no';" 2>/dev/null | tail -1)
if [ "$SEEDED" != "yes" ]; then
    echo "🌱 Running initial seeders..."
    php artisan db:seed --class=AdminSeeder --force
    php artisan db:seed --class=PortfolioSeeder --force
else
    echo "✅ Database already seeded, skipping"
fi

# ---- Generate Wayfinder routes ----
echo "🧭 Generating Wayfinder routes..."
php artisan wayfinder:generate 2>/dev/null || true

echo "✅ Application ready — starting services"

# ---- Start Supervisor (nginx + php-fpm + queue) ----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
