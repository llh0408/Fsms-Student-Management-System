#!/bin/bash
# Entrypoint script for PHP container
# This script runs before PHP-FPM starts

set -e

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Starting FSMS PHP container..."

# Wait for MySQL to be ready
echo "[$(date +'%Y-%m-%d %H:%M:%S')] Waiting for MySQL..."
while ! mysqladmin ping -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" --silent 2>/dev/null; do
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] MySQL is unavailable - sleeping..."
    sleep 2
done
echo "[$(date +'%Y-%m-%d %H:%M:%S')] MySQL is up!"

# Create necessary directories
echo "[$(date +'%Y-%m-%d %H:%M:%S')] Creating necessary directories..."
mkdir -p /var/www/html/htdocs/uploads/{assignments,avatars,challenges,submissions}

# Fix permissions
echo "[$(date +'%Y-%m-%d %H:%M:%S')] Fixing permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/htdocs/uploads

# Clear any cache if exists
if [ -d "/var/www/html/tmp" ]; then
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] Clearing cache..."
    rm -rf /var/www/html/tmp/*
fi

# Install Composer dependencies if composer.json exists
if [ -f "/var/www/html/composer.json" ] && [ ! -d "/var/www/html/vendor" ]; then
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] Installing Composer dependencies..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader
fi

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Container ready!"

# Execute the main process
exec "$@"
