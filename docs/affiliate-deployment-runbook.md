# Affiliate Deployment Runbook

This runbook describes the production deployment and background processing setup for the eiMaths Affiliate feature.

## Scope and assumptions

- Application: Laravel 10
- Required PHP version: PHP 8.1 or newer
- Server example: Ubuntu with Nginx, PHP-FPM, MySQL, and cron
- Application path: `/var/www/html/eiMaths_test`
- Application user: `eimaths_thailand2023`
- Web server user/group: `www-data`

The web server must point to the application's `public` directory, not the project root. See the [Laravel deployment documentation](https://laravel.com/docs/10.x/deployment).

## Required network access

Allow inbound traffic for:

- `22/tcp`: SSH, restricted to administrator IP addresses where possible.
- `80/tcp`: HTTP and certificate issuance.
- `443/tcp`: HTTPS.

Do not expose these ports publicly unless there is a specific infrastructure requirement:

- `3306/tcp`: MySQL; keep it private or bound to localhost.
- `6379/tcp`: Redis; not required by the current Affiliate configuration.
- `5173/tcp`: Vite development server; not required in production.

Allow outbound HTTPS for Google and other configured APIs. Allow outbound SMTP port `587` if the application sends mail through SMTP.

## Environment configuration

Edit the production environment file:

```bash
cd /var/www/html/eiMaths_test
sudo vim .env
```

The following values must be reviewed before production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE
DB_USERNAME=YOUR_DATABASE_USER
DB_PASSWORD=YOUR_DATABASE_PASSWORD

FILESYSTEM_DISK=local
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

GOOGLE_REDIRECT=https://YOUR_DOMAIN/auth/google/callback
AFFILIATE_VISITOR_HASH_KEY=64_HEX_CHARACTERS
AFFILIATE_ALLOWED_DESTINATION_HOSTS=YOUR_DOMAIN
SESSION_SECURE_COOKIE=true
```

Generate a visitor hash key without printing it to the terminal:

```bash
cd /var/www/html/eiMaths_test
HASH_KEY="$(php -r 'echo bin2hex(random_bytes(32));')"
sudo sed -i "s/^AFFILIATE_VISITOR_HASH_KEY=.*/AFFILIATE_VISITOR_HASH_KEY=${HASH_KEY}/" .env
unset HASH_KEY
```

If the `.env` file was created or modified with `sudo`, keep it readable by the application and PHP-FPM without making it world-readable:

```bash
sudo chown eimaths_thailand2023:www-data .env
sudo chmod 640 .env
```

If this is a new installation, generate the application key once:

```bash
php artisan key:generate --force
```

Do not regenerate `APP_KEY` on an existing installation unless the impact on encrypted data and sessions has been approved.

## Deployment commands

Run Artisan commands as `eimaths_thailand2023`, not with `sudo`, to avoid creating root-owned cache and log files.

```bash
cd /var/www/html/eiMaths_test

composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan storage:link

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Never run these destructive commands on a production database:

```bash
php artisan migrate:fresh
php artisan db:seed-fresh
```

Back up the database before applying production migrations.

## Writable directories

The application writes to Laravel storage directories and to several public upload directories:

```bash
cd /var/www/html/eiMaths_test

sudo mkdir -p storage/framework/{cache,data,sessions,views,image-conversion} \
  storage/logs bootstrap/cache public/uploads public/file_upload public/file_store \
  public/stamps public/img/qrcode

sudo chown -R eimaths_thailand2023:www-data storage bootstrap/cache \
  public/uploads public/file_upload public/file_store public/stamps public/img/qrcode

sudo chmod -R ug+rwX storage bootstrap/cache \
  public/uploads public/file_upload public/file_store public/stamps public/img/qrcode
```

Do not make the entire project directory world-writable.

## Affiliate background processing

The Affiliate scheduler currently defines these tasks in `app/Console/Kernel.php`:

- `affiliate:release-commissions`: every minute.
- `affiliate:reconcile-wallets`: daily at `01:30`.
- `affiliate:prune-click-events`: daily at `02:15`.

Only one cron entry is required. Laravel evaluates the schedule and decides which task is due. See the [Laravel scheduling documentation](https://laravel.com/docs/10.x/scheduling).

Open the current user's crontab with Vim:

```bash
cd /var/www/html/eiMaths_test
EDITOR=vim crontab -e
```

In Vim:

1. Press `i` to enter insert mode.
2. Add this line:

   ```cron
   * * * * * cd /var/www/html/eiMaths_test && /usr/bin/php artisan schedule:run >> /var/www/html/eiMaths_test/storage/logs/scheduler.log 2>&1
   ```

3. Press `Esc`.
4. Type `:wq` and press `Enter` to save and exit.

Verify the cron entry and cron service:

```bash
crontab -l
sudo systemctl enable --now cron
sudo systemctl is-active cron
```

Check the registered schedule:

```bash
cd /var/www/html/eiMaths_test
php artisan schedule:list
```

The current configuration uses `QUEUE_CONNECTION=sync`, so a queue worker, Supervisor, and Redis are not required for the current baseline deployment. The preflight command warns that `sync` is not a durable asynchronous queue. If the application is later changed to `database` or `redis` queues, configure a persistent queue worker under Supervisor before enabling that setting.

## Affiliate verification

Run these commands after changing `.env` or deploying new code:

```bash
cd /var/www/html/eiMaths_test

php artisan optimize:clear
php artisan config:cache
php artisan affiliate:preflight
php artisan schedule:list
```

The preflight check must not report:

```text
[BLOCKED] visitor_hash_key
```

A safe way to verify the key length without displaying the key is:

```bash
awk -F= '/^AFFILIATE_VISITOR_HASH_KEY=/{print "hash key length=" length($2)}' .env
```

Expected output is at least 32 characters; a 64-character hexadecimal key is recommended.

After the next cron interval, inspect the scheduler log:

```bash
tail -n 50 /var/www/html/eiMaths_test/storage/logs/scheduler.log
```

Do not repeatedly run `php artisan schedule:run` manually in production because the commission and wallet commands can change financial records.

## Production security notes

- Do not copy `.env.example` into production without replacing all secrets and environment-specific values.
- If credential-looking values in `.env.example` are real, rotate them.
- Keep `APP_DEBUG=false` in production.
- Confirm the Google OAuth callback URL matches the production domain.
- Confirm `AFFILIATE_ALLOWED_DESTINATION_HOSTS` contains the intended production hostnames.
- Confirm Affiliate financial, withdrawal, refund, and Credit Note feature flags with the responsible business and Finance owners before enabling them.
- The repository currently does not track `composer.lock`; include a reviewed lock file in the release artifact for deterministic dependency installation. Avoid running `composer update` directly on production.
