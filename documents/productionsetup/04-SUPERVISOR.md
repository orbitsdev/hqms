# Supervisor Configuration

Supervisor manages the Laravel queue workers and Laravel Reverb (WebSocket server) processes.

## Laravel Queue Worker

The queue worker processes background jobs like notifications, emails, and other async tasks.

### Create Queue Worker Configuration

```bash
sudo nano /etc/supervisor/conf.d/hqms-worker.conf
```

```ini
[program:hqms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hqms/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hqms/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

**Configuration explained:**
- `numprocs=2` - Run 2 worker processes (adjust based on server capacity)
- `--sleep=3` - Sleep 3 seconds when no jobs available
- `--tries=3` - Retry failed jobs 3 times
- `--max-time=3600` - Restart worker every hour (prevents memory leaks)
- `stopwaitsecs=3600` - Wait up to 1 hour for long jobs to finish

## Laravel Reverb (WebSocket Server)

Reverb handles real-time WebSocket connections for queue display updates, notifications, etc.

### Create Reverb Configuration

```bash
sudo nano /etc/supervisor/conf.d/hqms-reverb.conf
```

```ini
[program:hqms-reverb]
process_name=%(program_name)s
command=php /var/www/hqms/artisan reverb:start --host=127.0.0.1 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/hqms/storage/logs/reverb.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=10
```

**Important notes:**
- `--host=127.0.0.1` - Only accept connections from localhost (Nginx will proxy)
- `--port=8080` - Port for WebSocket server
- `numprocs=1` - Only one Reverb process needed

## Laravel Scheduler (Cron)

The scheduler doesn't use Supervisor. Instead, add a cron job:

```bash
# Edit crontab for deploy user
crontab -e
```

Add this line:
```cron
* * * * * cd /var/www/hqms && php artisan schedule:run >> /dev/null 2>&1
```

## Update and Start Supervisor

```bash
# Re-read configuration files
sudo supervisorctl reread

# Update Supervisor with new programs
sudo supervisorctl update

# Start all programs
sudo supervisorctl start all
```

## Supervisor Commands

```bash
# Check status of all processes
sudo supervisorctl status

# Expected output:
# hqms-reverb                      RUNNING   pid 12345, uptime 0:05:00
# hqms-worker:hqms-worker_00       RUNNING   pid 12346, uptime 0:05:00
# hqms-worker:hqms-worker_01       RUNNING   pid 12347, uptime 0:05:00

# Start a specific program
sudo supervisorctl start hqms-worker:*

# Stop a specific program
sudo supervisorctl stop hqms-worker:*

# Restart a specific program
sudo supervisorctl restart hqms-worker:*

# Restart all programs
sudo supervisorctl restart all

# Reload configuration (after editing .conf files)
sudo supervisorctl reread
sudo supervisorctl update

# View real-time logs
sudo supervisorctl tail -f hqms-worker:hqms-worker_00
sudo supervisorctl tail -f hqms-reverb
```

## Queue Worker for Multiple Queues

If you have different queues with priorities:

```bash
sudo nano /etc/supervisor/conf.d/hqms-worker.conf
```

```ini
[program:hqms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hqms/artisan queue:work database --queue=high,default,low --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hqms/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

The `--queue=high,default,low` processes jobs in priority order.

## Log Rotation

Configure log rotation to prevent disk space issues:

```bash
sudo nano /etc/logrotate.d/hqms
```

```
/var/www/hqms/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 deploy www-data
    sharedscripts
    postrotate
        supervisorctl restart hqms-worker:* > /dev/null 2>&1 || true
        supervisorctl restart hqms-reverb > /dev/null 2>&1 || true
    endscript
}
```

Test log rotation:
```bash
sudo logrotate -d /etc/logrotate.d/hqms
```

## Monitoring Queue Health

### Check Queue Size

```bash
# SSH into server and run
cd /var/www/hqms
php artisan queue:monitor database:default --max=100
```

### Queue Batching Status

```bash
php artisan queue:batches
```

### Clear Failed Jobs

```bash
# List failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush
```

## Reverb Health Check

```bash
# Check if Reverb is running
curl -i http://127.0.0.1:8080

# Check WebSocket connection (requires wscat)
# npm install -g wscat
# wscat -c ws://127.0.0.1:8080/app/your-app-key

# View Reverb logs
tail -f /var/www/hqms/storage/logs/reverb.log
```

## Horizon (Alternative to Basic Queue Worker)

For more advanced queue management, consider Laravel Horizon:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Supervisor config for Horizon:
```ini
[program:hqms-horizon]
process_name=%(program_name)s
command=php /var/www/hqms/artisan horizon
autostart=true
autorestart=true
user=deploy
redirect_stderr=true
stdout_logfile=/var/www/hqms/storage/logs/horizon.log
stopwaitsecs=3600
```

## Troubleshooting

### Workers Not Starting

```bash
# Check Supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log

# Check worker logs
tail -f /var/www/hqms/storage/logs/worker.log
```

### Jobs Not Processing

```bash
# Check if jobs exist in queue
php artisan tinker
>>> DB::table('jobs')->count()

# Manually process one job
php artisan queue:work --once
```

### Reverb Connection Issues

```bash
# Check if port is listening
sudo netstat -tlnp | grep 8080

# Check firewall
sudo ufw status

# Check Nginx proxy logs
sudo tail -f /var/log/nginx/error.log
```

### Memory Issues

If workers consume too much memory:

```ini
# Add memory limit to command
command=php /var/www/hqms/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=256
```

**Next**: [05-ENV-PRODUCTION.md](./05-ENV-PRODUCTION.md) - Production environment configuration
