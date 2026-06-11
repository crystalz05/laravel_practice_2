#!/bin/sh
set -e

echo "==> Starting blog-api entrypoint..."

# ── Create .env if it doesn't exist ────────────────────────────────────────
if [ ! -f /var/www/html/.env ]; then
    echo "==> .env not found, copying from .env.example"
    cp /var/www/html/.env.example /var/www/html/.env
fi

# ── Inject runtime env vars into .env (Render sets them as process envs) ──
# These overwrite any file-level values so Docker env vars always win.
for var in APP_KEY APP_ENV APP_URL APP_DEBUG \
           DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SSLMODE \
           CACHE_STORE SESSION_DRIVER QUEUE_CONNECTION \
           MAIL_MAILER MAIL_HOST MAIL_PORT MAIL_USERNAME MAIL_PASSWORD MAIL_FROM_ADDRESS; do
    val=$(printenv "$var" 2>/dev/null || true)
    if [ -n "$val" ]; then
        # Replace or append the variable in .env (wrapped in quotes to handle special characters like #)
        if grep -q "^${var}=" /var/www/html/.env; then
            sed -i "s|^${var}=.*|${var}=\"${val}\"|" /var/www/html/.env
        else
            echo "${var}=\"${val}\"" >> /var/www/html/.env
        fi
    fi
done

# ── Generate app key if missing ────────────────────────────────────────────
if grep -q "^APP_KEY=$" /var/www/html/.env || [ -z "$(grep '^APP_KEY=' /var/www/html/.env | cut -d= -f2)" ]; then
    echo "==> Generating APP_KEY..."
    php artisan key:generate --force
fi

# ── Ensure storage dirs exist and are writable ─────────────────────────────
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# ── Run migrations ─────────────────────────────────────────────────────────
echo "==> Running migrations..."
php artisan migrate --force

# ── Cache config/routes/views for performance ──────────────────────────────
echo "==> Caching Laravel config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Start supervisor (nginx + php-fpm + queue) ─────────────────────────────
echo "==> Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
