#!/bin/bash
set -e

# Configure Apache port based on Render's dynamic $PORT
PORT=${PORT:-80}
echo "Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Setup .env file if not exists
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env 2>/dev/null || touch /var/www/html/.env
fi

# Ensure SQLite file exists
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi
sqlite3 /var/www/html/database/database.sqlite "PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;" 2>/dev/null || true

# Fix permissions
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

# Run Laravel optimizations and migrations
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan migrate --force || true
php artisan db:seed --force || true
php artisan storage:link || true

echo "Starting Apache Web Server..."
exec apache2-foreground

