#!/bin/sh
# Wait for DB (optional, retries 10 times)
i=0
until php artisan migrate:status > /dev/null 2>&1 || [ $i -eq 10 ]; do
  echo "Waiting for database..."
  i=$((i+1))
  sleep 3
done

# Run migrations & cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Nginx and PHP-FPM
service nginx start
php-fpm