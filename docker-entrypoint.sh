#!/bin/sh
set -e

echo "=== Initializing VoucherNet Application ==="

# Check if .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure storage and bootstrap/cache permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/app/public/proofs \
         /var/www/html/storage/app/public/vouchers \
         /var/www/html/storage/app/public/receipts \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

# Ensure SQLite file exists
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating database/database.sqlite..."
    touch /var/www/html/database/database.sqlite
fi

chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Install composer dependencies if vendor doesn't exist
if [ ! -d /var/www/html/vendor ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-audit
fi

# Generate application key if empty
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "Generating Application Key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running Migrations..."
php artisan migrate --force

# Run seeder only if users table is empty (prevent resetting changed passwords on restart)
USER_COUNT=$(php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo \App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "First time database setup detected, running initial seeders..."
    php artisan db:seed --force
else
    echo "Database already contains users (${USER_COUNT} users), skipping seeders to preserve custom passwords and data."
fi

# Create public storage link
php artisan storage:link || true

echo "=== VoucherNet Ready! Starting Web Server on http://0.0.0.0:8000 ==="

exec "$@"
