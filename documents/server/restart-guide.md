# Restart Guide - CareTime Server

## When to Restart Queue Workers

Restart `hqms-worker` when you change:

- `.env` values
- Job classes in `app/Jobs/`
- Event/listener code
- Deploy new code (`git pull`)
- `config/queue.php`

```bash
sudo supervisorctl restart hqms-worker:*
```

## When to Restart Reverb

Restart `hqms-reverb` when you change:

- `.env` (REVERB_* values)
- `config/reverb.php`
- Broadcast channel definitions (`routes/channels.php`)
- Deploy new code (`git pull`)

```bash
sudo supervisorctl restart hqms-reverb
```

## No Restart Needed

- Blade/view changes
- Frontend CSS/JS changes (just run `npm run build`)
- Database migrations
- Adding new routes (run `php artisan route:clear` instead)

## Quick Commands

```bash
# Restart both after deployment
sudo supervisorctl restart all

# Restart only workers
sudo supervisorctl restart hqms-worker:*

# Restart only Reverb
sudo supervisorctl restart hqms-reverb

# Check status
sudo supervisorctl status

# View logs
tail -f /var/www/hqms/storage/logs/worker.log
tail -f /var/www/hqms/storage/logs/reverb.log
```

## After Deployment Checklist

```bash
cd /var/www/hqms

# 1. Pull latest code
git pull origin main

# 2. Install dependencies (if composer.json changed)
composer install --no-dev --optimize-autoloader

# 3. Run migrations (if any)
php artisan migrate --force

# 4. Build frontend (if assets changed)
npm run build

# 5. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Restart workers
sudo supervisorctl restart all
```
