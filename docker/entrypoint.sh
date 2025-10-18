#!/bin/sh
set -e

echo "========================================="
echo "Starting Laravel Application Setup"
echo "========================================="

# Create required directories
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Wait for database to be ready
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for database connection..."
    max_attempts=60
    attempt=0

    until php artisan db:show 2>/dev/null || [ $attempt -eq $max_attempts ]; do
        attempt=$((attempt + 1))
        echo "Database not ready yet (attempt $attempt/$max_attempts)..."
        sleep 2
    done

    if [ $attempt -eq $max_attempts ]; then
        echo "⚠️  Warning: Could not connect to database after $max_attempts attempts"
        echo "Continuing anyway - migrations will fail if DB is required"
    else
        echo "✅ Database connection successful"
        
        # Run migrations (without cache locks for fresh database)
        echo "🔄 Running database migrations..."
        CACHE_DRIVER=array php artisan migrate --force || echo "⚠️  Migrations failed or not needed"
    fi
fi

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link --force 2>/dev/null || echo "Storage link already exists"

# Clear and optimize Laravel
echo "⚡ Optimizing Laravel application..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

# Final permission check
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "========================================="
echo "✅ Setup completed successfully!"
echo "========================================="

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf