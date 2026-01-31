# Supervisor Config - CareTime Server (146.190.100.242)

## 1. Queue Worker

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
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hqms/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=3600
```

## 2. Reverb WebSocket

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
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/hqms/storage/logs/reverb.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=10
```

## 3. After saving both files, run:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status
```

## Expected output:

```
hqms-reverb                      RUNNING   pid 12345, uptime 0:00:05
hqms-worker:hqms-worker_00       RUNNING   pid 12346, uptime 0:00:05
hqms-worker:hqms-worker_01       RUNNING   pid 12347, uptime 0:00:05
```

## Useful commands:

```bash
# Check status
sudo supervisorctl status

# Restart all
sudo supervisorctl restart all

# View logs
tail -f /var/www/hqms/storage/logs/reverb.log
tail -f /var/www/hqms/storage/logs/worker.log
```
