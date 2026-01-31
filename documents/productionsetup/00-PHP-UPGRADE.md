# PHP Upgrade Guide - DigitalOcean LEMP Stack

This guide covers upgrading PHP on an existing DigitalOcean droplet with LEMP stack to PHP 8.4 (required for Laravel 12).

## Prerequisites

- Existing DigitalOcean droplet with Ubuntu 22.04/24.04
- LEMP stack installed (Linux, Nginx, MySQL, PHP)
- SSH access to your server

## Step 1: Check Current PHP Version

```bash
# Check CLI version
php -v

# Check PHP-FPM version
php-fpm -v

# List installed PHP packages
dpkg -l | grep php
```

Note your current PHP version (e.g., 7.4, 8.0, 8.1, 8.2, 8.3) for later cleanup.

## Step 2: Add Ondrej PHP Repository

The official Ubuntu repositories may not have the latest PHP. We'll use the trusted Ondrej PPA:

```bash
# Install prerequisites
sudo apt update
sudo apt install software-properties-common -y

# Add Ondrej PHP repository
sudo add-apt-repository ppa:ondrej/php -y

# Update package lists
sudo apt update
```

## Step 3: Install PHP 8.4 and Required Extensions

Install PHP 8.4 with all extensions required by Laravel 12:

```bash
sudo apt install php8.4-fpm php8.4-cli php8.4-common php8.4-mysql \
    php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd \
    php8.4-redis php8.4-bcmath php8.4-intl php8.4-readline \
    php8.4-sqlite3 php8.4-tokenizer -y
```

### Verify Installation

```bash
# Check new version
php8.4 -v

# Should output something like:
# PHP 8.4.x (cli) (built: ...)
```

## Step 4: Configure PHP 8.4 FPM

### 4.1 Edit PHP-FPM Pool Configuration

```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

**Important settings** (adjust based on your droplet RAM):

For **4GB RAM** droplet:
```ini
user = www-data
group = www-data

listen = /run/php/php8.4-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

For **2GB RAM** droplet:
```ini
pm = dynamic
pm.max_children = 25
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10
pm.max_requests = 500
```

For **1GB RAM** droplet:
```ini
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 5
pm.max_requests = 500
```

### 4.2 Edit PHP.ini Settings

```bash
sudo nano /etc/php/8.4/fpm/php.ini
```

Find and modify these settings for production:

```ini
; File uploads
upload_max_filesize = 50M
post_max_size = 50M

; Memory and execution
memory_limit = 512M
max_execution_time = 300

; Error handling (production)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Timezone (adjust to your location)
date.timezone = Asia/Manila

; OPcache for performance
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.save_comments = 1
```

Create error log directory:
```bash
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php
```

### 4.3 Start PHP 8.4 FPM

```bash
sudo systemctl enable php8.4-fpm
sudo systemctl start php8.4-fpm
sudo systemctl status php8.4-fpm
```

## Step 5: Update Nginx Configuration

Update your Nginx site configuration to use PHP 8.4 FPM socket.

```bash
sudo nano /etc/nginx/sites-available/hqms
```

Find the PHP-FPM socket line and update it:

```nginx
# OLD (example - your version may vary)
# fastcgi_pass unix:/run/php/php8.1-fpm.sock;
# fastcgi_pass unix:/run/php/php7.4-fpm.sock;

# NEW - PHP 8.4
fastcgi_pass unix:/run/php/php8.4-fpm.sock;
```

### Full Nginx PHP Location Block

Make sure your location block looks like this:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
    fastcgi_hide_header X-Powered-By;
}
```

### Test and Reload Nginx

```bash
# Test configuration
sudo nginx -t

# If test passes, reload
sudo systemctl reload nginx
```

## Step 6: Update CLI Default PHP Version

Set PHP 8.4 as the default CLI version:

```bash
# Update alternatives for PHP CLI
sudo update-alternatives --set php /usr/bin/php8.4

# Verify
php -v
# Should show PHP 8.4.x
```

If the above doesn't work, use interactive selection:
```bash
sudo update-alternatives --config php
# Select the number corresponding to PHP 8.4
```

## Step 7: Stop Old PHP-FPM Service

Stop and disable your old PHP-FPM version:

```bash
# Replace X.X with your old version (e.g., 8.1, 8.2, 7.4)
sudo systemctl stop php8.1-fpm
sudo systemctl disable php8.1-fpm

# Or for PHP 7.4
sudo systemctl stop php7.4-fpm
sudo systemctl disable php7.4-fpm
```

## Step 8: Verify Everything Works

### 8.1 Check PHP Version
```bash
php -v
# Should show PHP 8.4.x
```

### 8.2 Check PHP-FPM is Running
```bash
sudo systemctl status php8.4-fpm
# Should show "active (running)"
```

### 8.3 Check Nginx is Using PHP 8.4
```bash
# Check socket exists
ls -la /run/php/php8.4-fpm.sock
```

### 8.4 Test from Browser
Create a PHP info file (temporarily):
```bash
echo "<?php phpinfo();" | sudo tee /var/www/hqms/public/phpinfo.php
```

Visit `https://your-domain.com/phpinfo.php` and verify PHP 8.4 is shown.

**IMPORTANT: Remove the phpinfo file immediately after testing:**
```bash
sudo rm /var/www/hqms/public/phpinfo.php
```

### 8.5 Test Laravel Application
```bash
cd /var/www/hqms
php artisan --version
# Should work without errors

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Restart queue workers if using Supervisor
sudo supervisorctl restart all
```

## Step 9: Clean Up Old PHP Versions (Optional)

After confirming everything works for a few days, remove old PHP packages:

```bash
# List all PHP packages
dpkg -l | grep php

# Remove old PHP version (replace X.X with version number)
sudo apt remove php8.1-* -y
sudo apt remove php7.4-* -y

# Clean up
sudo apt autoremove -y
```

## Troubleshooting

### 502 Bad Gateway Error

This usually means PHP-FPM isn't running or Nginx can't connect to the socket.

```bash
# Check PHP-FPM status
sudo systemctl status php8.4-fpm

# Check socket exists
ls -la /run/php/

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

### "Class not found" or Extension Errors

Missing PHP extension. Install the required extension:

```bash
# Example: install missing extension
sudo apt install php8.4-[extension-name]
sudo systemctl restart php8.4-fpm
```

Common extensions:
```bash
# If you see GD errors
sudo apt install php8.4-gd

# If you see Redis errors
sudo apt install php8.4-redis

# If you see SQLite errors
sudo apt install php8.4-sqlite3
```

### Composer Memory Issues

```bash
# Increase memory for Composer
php -d memory_limit=-1 /usr/local/bin/composer install
```

### OPcache Not Working

```bash
# Check OPcache status
php -i | grep opcache

# If disabled, install it
sudo apt install php8.4-opcache
sudo systemctl restart php8.4-fpm
```

## Quick Reference Commands

```bash
# PHP version
php -v

# PHP-FPM status
sudo systemctl status php8.4-fpm

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart Nginx
sudo systemctl restart nginx

# View PHP-FPM logs
sudo tail -f /var/log/php8.4-fpm.log

# View Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check which PHP FPM sockets exist
ls -la /run/php/
```

## Summary

After completing this guide:
- [x] PHP 8.4 installed with all required extensions
- [x] PHP-FPM configured and running
- [x] Nginx updated to use PHP 8.4 socket
- [x] CLI PHP set to 8.4
- [x] Old PHP-FPM stopped
- [x] Application tested and working

**Next**: Continue with [01-SERVER-SETUP.md](./01-SERVER-SETUP.md) if setting up a new server, or [03-LARAVEL-DEPLOYMENT.md](./03-LARAVEL-DEPLOYMENT.md) if deploying/updating the application.
