#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# ---- Wait for database ----
echo "⏳ Waiting for PostgreSQL..."
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-laravel}" -q; do
    sleep 2
done
echo "✅ PostgreSQL is ready"

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
