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

# Run database migrations and seeder
echo "Running Migrations and Seeders..."
php artisan migrate --force
php artisan db:seed --force

# Create public storage link
php artisan storage:link || true

echo "=== VoucherNet Ready! Starting Web Server on http://0.0.0.0:8000 ==="

exec "$@"
