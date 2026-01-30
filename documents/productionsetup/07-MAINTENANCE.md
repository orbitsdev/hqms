# Maintenance, Backups, and Monitoring

## Daily Operations

### Check System Health

```bash
# Server uptime and load
uptime

# Disk usage
df -h

# Memory usage
free -h

# Running processes
htop
```

### Check Application Services

```bash
# All services status
sudo systemctl status nginx php8.4-fpm mysql redis-server supervisor

# Queue workers and Reverb
sudo supervisorctl status

# Recent errors
tail -50 /var/www/hqms/storage/logs/laravel.log
```

## Database Backups

### Manual Backup

```bash
# Create backup directory
mkdir -p /home/deploy/backups

# Backup database
mysqldump -u hqms_user -p hqms > /home/deploy/backups/hqms_$(date +%Y%m%d_%H%M%S).sql

# Compress backup
gzip /home/deploy/backups/hqms_$(date +%Y%m%d_%H%M%S).sql
```

### Automated Daily Backups

Create backup script:
```bash
nano /home/deploy/scripts/backup-database.sh
```

```bash
#!/bin/bash

# Configuration
DB_NAME="hqms"
DB_USER="hqms_user"
DB_PASS="your_database_password"
BACKUP_DIR="/home/deploy/backups"
RETENTION_DAYS=30

# Create backup directory if not exists
mkdir -p $BACKUP_DIR

# Create backup with timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql.gz"

# Dump and compress
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_FILE

# Delete backups older than retention period
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete

# Log
echo "$(date): Backup created: $BACKUP_FILE" >> $BACKUP_DIR/backup.log
```

Make executable and add to cron:
```bash
chmod +x /home/deploy/scripts/backup-database.sh

# Add to crontab
crontab -e
```

Add this line for daily backup at 2 AM:
```cron
0 2 * * * /home/deploy/scripts/backup-database.sh
```

### Restore Database

```bash
# Decompress if needed
gunzip /home/deploy/backups/hqms_20260129.sql.gz

# Restore
mysql -u hqms_user -p hqms < /home/deploy/backups/hqms_20260129.sql
```

## Application Backups

### Full Application Backup

```bash
nano /home/deploy/scripts/backup-app.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/home/deploy/backups"
APP_DIR="/var/www/hqms"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Backup uploaded files and .env
tar -czvf $BACKUP_DIR/hqms_files_$TIMESTAMP.tar.gz \
    $APP_DIR/storage/app \
    $APP_DIR/.env

# Keep only last 7 days
find $BACKUP_DIR -name "hqms_files_*.tar.gz" -mtime +7 -delete
```

### Off-site Backup (DigitalOcean Spaces / S3)

```bash
# Install AWS CLI
sudo apt install awscli -y

# Configure credentials
aws configure

# Sync backups to S3/Spaces
aws s3 sync /home/deploy/backups s3://your-bucket/hqms-backups --delete
```

Add to cron:
```cron
30 2 * * * aws s3 sync /home/deploy/backups s3://your-bucket/hqms-backups
```

## Deployment Updates

### Standard Deployment

```bash
cd /var/www/hqms
./deploy.sh
```

### Manual Deployment Steps

```bash
# Enable maintenance mode
php artisan down --retry=60

# Pull changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# Restart workers
sudo supervisorctl restart hqms-worker:*
sudo supervisorctl restart hqms-reverb

# Disable maintenance mode
php artisan up
```

### Rollback Deployment

```bash
# Go to previous commit
git log --oneline -5
git checkout <previous-commit-hash>

# Or revert last commit
git revert HEAD --no-edit

# Re-run deployment steps
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback --step=1
php artisan cache:clear
php artisan config:cache
```

## Log Management

### View Logs

```bash
# Laravel application logs
tail -f /var/www/hqms/storage/logs/laravel.log

# Queue worker logs
tail -f /var/www/hqms/storage/logs/worker.log

# Reverb logs
tail -f /var/www/hqms/storage/logs/reverb.log

# Nginx access log
sudo tail -f /var/log/nginx/access.log

# Nginx error log
sudo tail -f /var/log/nginx/error.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log
```

### Log Rotation

Laravel logs are handled by the application. System logs use logrotate.

Check logrotate config:
```bash
cat /etc/logrotate.d/hqms
```

### Clear Old Logs

```bash
# Clear Laravel logs older than 7 days
find /var/www/hqms/storage/logs -name "*.log" -mtime +7 -delete

# Or truncate current log
truncate -s 0 /var/www/hqms/storage/logs/laravel.log
```

## Monitoring

### Basic Server Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Monitor CPU/Memory
htop

# Monitor disk I/O
sudo iotop

# Monitor network
sudo nethogs
```

### Application Monitoring with Laravel Pulse (Optional)

```bash
composer require laravel/pulse

php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate

# Access at: https://your-domain.com/pulse
```

### Uptime Monitoring Services

- **UptimeRobot** (free tier available)
- **Better Uptime**
- **Pingdom**

Set up HTTP checks for:
- `https://your-domain.com` (main site)
- `https://your-domain.com/api/health` (API health check)

### Create Health Check Endpoint

Add to `routes/api.php`:
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'error',
        'cache' => Cache::put('health', 'ok', 10) ? 'working' : 'error',
        'queue' => Queue::size() >= 0 ? 'working' : 'error',
    ]);
});
```

### Error Tracking with Sentry (Optional)

```bash
composer require sentry/sentry-laravel

php artisan sentry:publish --dsn=your-sentry-dsn
```

Add to `.env`:
```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn
SENTRY_TRACES_SAMPLE_RATE=0.1
```

## Security Maintenance

### Regular Security Updates

```bash
# Update system packages weekly
sudo apt update && sudo apt upgrade -y

# Check for Laravel security advisories
composer audit
```

### Check for Failed Login Attempts

```bash
# Check auth log
sudo grep "Failed password" /var/log/auth.log | tail -20

# Check Laravel failed logins (if logging enabled)
grep "login failed" /var/www/hqms/storage/logs/laravel.log
```

### Rotate Credentials Periodically

1. **Database password** - Quarterly
2. **APP_KEY** - Annually (invalidates sessions)
3. **API keys** - As needed or after personnel changes

```bash
# After changing .env
php artisan config:cache
sudo supervisorctl restart all
```

## Scheduled Tasks (Cron Setup)

### Setup Laravel Scheduler

The Laravel scheduler requires a single cron entry that runs every minute:

```bash
# Edit crontab for deploy user
crontab -e
```

Add this line:
```cron
* * * * * cd /var/www/hqms && php artisan schedule:run >> /dev/null 2>&1
```

**Alternative with logging:**
```cron
* * * * * cd /var/www/hqms && php artisan schedule:run >> /var/www/hqms/storage/logs/scheduler.log 2>&1
```

### Verify Scheduler is Working

```bash
# List all scheduled tasks
php artisan schedule:list

# Run scheduler manually to test
php artisan schedule:run

# Check scheduler logs
sudo grep CRON /var/log/syslog | tail -20

# Or check custom log (if using alternative above)
tail -f /var/www/hqms/storage/logs/scheduler.log
```

### Laravel 12 Scheduler Location

In Laravel 12, scheduled tasks are defined in `bootstrap/app.php` using `withSchedule()`:

```php
// bootstrap/app.php
->withSchedule(function (Schedule $schedule): void {
    $schedule->command('cache:prune-stale-tags')->daily();
    $schedule->call(function () {
        // Custom cleanup logic
    })->daily()->at('00:00');
})
```

> **Note:** In Laravel 12, do NOT use `routes/console.php` for scheduling.
> That file is only for custom Artisan commands.

### Current Scheduled Tasks

The system includes these automated tasks (defined in `bootstrap/app.php`):

| Task | Schedule | Description |
|------|----------|-------------|
| Cleanup old queues | Daily 00:00 | Delete queue records older than 90 days |
| Prune notifications | Daily 01:00 | Delete read notifications older than 30 days |
| Cleanup SMS logs | Weekly Sun 02:00 | Delete sent SMS logs older than 60 days |
| Cache cleanup | Daily 03:00 | Prune stale cache tags |
| Optimize | Daily 04:00 | Rebuild config/route/view caches (prod only) |
| Health check | Hourly | Log scheduler health status |

### Full Production Crontab Example

```bash
crontab -e
```

```cron
# Laravel Scheduler (runs every minute) - REQUIRED
* * * * * cd /var/www/hqms && php artisan schedule:run >> /dev/null 2>&1

# Database Backup (daily at 2 AM)
0 2 * * * /home/deploy/scripts/backup-database.sh

# Application Backup (daily at 2:30 AM)
30 2 * * * /home/deploy/scripts/backup-app.sh

# Sync backups to cloud (daily at 3 AM)
0 3 * * * aws s3 sync /home/deploy/backups s3://your-bucket/hqms-backups --delete

# System updates check (weekly on Sunday 5 AM)
0 5 * * 0 sudo apt update && sudo apt upgrade -y >> /var/log/apt-upgrade.log 2>&1
```

### Troubleshooting Cron

```bash
# Check if cron service is running
sudo systemctl status cron

# View cron logs
sudo grep CRON /var/log/syslog | tail -20

# Check crontab for current user
crontab -l

# Check crontab for deploy user
sudo crontab -u deploy -l

# Test scheduler manually
cd /var/www/hqms && php artisan schedule:list
cd /var/www/hqms && php artisan schedule:run
```

## Performance Optimization

### OPcache Status

```bash
# Check OPcache (should be enabled)
php -i | grep opcache

# Clear OPcache after deployments
sudo systemctl restart php8.4-fpm
```

### MySQL Optimization

```bash
# Check slow queries
sudo mysqladmin -u root -p status

# Enable slow query log
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Add:
# slow_query_log = 1
# slow_query_log_file = /var/log/mysql/slow.log
# long_query_time = 2

sudo systemctl restart mysql
```

### Redis Memory

```bash
# Check Redis memory usage
redis-cli info memory

# Clear Redis cache if needed
redis-cli FLUSHDB
```

## Emergency Procedures

### Application Down

```bash
# Check services
sudo systemctl status nginx php8.4-fpm mysql

# Restart services
sudo systemctl restart nginx php8.4-fpm mysql

# Check application logs
tail -100 /var/www/hqms/storage/logs/laravel.log
```

### Database Corruption

```bash
# Check MySQL
sudo mysqlcheck -u root -p --all-databases

# Repair if needed
sudo mysqlcheck -u root -p --repair hqms
```

### Disk Full

```bash
# Check disk usage
df -h
du -sh /var/www/hqms/storage/logs/*

# Clear logs
truncate -s 0 /var/www/hqms/storage/logs/*.log

# Clear old backups
find /home/deploy/backups -mtime +7 -delete
```

### Out of Memory

```bash
# Check memory
free -h

# Restart memory-heavy services
sudo systemctl restart php8.4-fpm mysql

# Reduce queue workers if needed
# Edit /etc/supervisor/conf.d/hqms-worker.conf
# Change numprocs=2 to numprocs=1
sudo supervisorctl reread
sudo supervisorctl update
```

## Contact Information

Keep emergency contacts documented:

- **Server Provider**: DigitalOcean Support
- **Domain Registrar**: Support contact
- **Developer**: developer@email.com
- **System Admin**: admin@email.com

---

## Quick Reference Commands

```bash
# Deploy
./deploy.sh

# Maintenance mode
php artisan down
php artisan up

# Logs
tail -f storage/logs/laravel.log

# Restart services
sudo supervisorctl restart all
sudo systemctl restart nginx php8.4-fpm

# Backup database
mysqldump -u hqms_user -p hqms | gzip > backup.sql.gz

# Clear all caches
php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```
