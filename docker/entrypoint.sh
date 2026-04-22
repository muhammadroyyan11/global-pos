#!/bin/bash
set -e

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=db;port=3306;dbname=pos_app', 'pos_user', 'secret');" 2>/dev/null; do
    sleep 2
done
echo "MySQL ready!"

# Generate key jika belum ada
if [ -z "$(grep '^APP_KEY=base64' /var/www/.env)" ]; then
    php artisan key:generate --force
fi

# Migrate — seed hanya jika tabel users kosong
php artisan migrate --force

USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php-fpm
