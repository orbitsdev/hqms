# Laravel Deployment

## Clone the Repository

```bash
# Switch to deploy user
su - deploy

# Navigate to web directory
cd /var/www

# Clone repository (replace with your actual repo URL)
git clone https://github.com/your-username/hqms.git hqms

# Or if private repo with deploy key:
# git clone git@github.com:your-username/hqms.git hqms

cd hqms
```

## Install PHP Dependencies

```bash
# Install without dev dependencies
composer install --no-dev --optimize-autoloader
```

If you get memory issues:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader
```

## Install and Build Frontend Assets

```bash
# Install npm dependencies
npm ci

# Build for production
npm run build
```

## Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

See [05-ENV-PRODUCTION.md](./05-ENV-PRODUCTION.md) for detailed environment configuration.

## Set Directory Permissions

```bash
# Set ownership
sudo chown -R deploy:www-data /var/www/hqms

# Set directory permissions
sudo find /var/www/hqms -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/hqms -type f -exec chmod 644 {} \;

# Make storage and cache writable
sudo chmod -R 775 /var/www/hqms/storage
sudo chmod -R 775 /var/www/hqms/bootstrap/cache

# Ensure www-data can write to these directories
sudo chgrp -R www-data /var/www/hqms/storage
sudo chgrp -R www-data /var/www/hqms/bootstrap/cache
```

## Create Storage Link

```bash
php artisan storage:link
```

## Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (if applicable)
php artisan db:seed --force
```

## Cache Configuration for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache icons (Blade Icons / Flux)
php artisan icons:cache
```

## Verify Installation

```bash
# Check Laravel version
php artisan --version

# Check environment
php artisan env

# Should show: Current application environment: production
```

## Test the Application

1. Open your browser and navigate to: `http://your-domain.com`
2. You should see the HQMS login page
3. Check the logs if there are errors:

```bash
# Laravel logs
tail -f /var/www/hqms/storage/logs/laravel.log

# Nginx error logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.4-fpm.log
```

## Deployment Script (deploy.sh)

Create a deployment script for future updates:

```bash
nano /var/www/hqms/deploy.sh
```

```bash
#!/bin/bash

# HQMS Deployment Script
# Usage: ./deploy.sh

set -e

echo "Starting deployment..."

# Navigate to application directory
cd /var/www/hqms

# Enable maintenance mode
php artisan down --retry=60

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader

# Install npm dependencies and build
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# Restart queue workers (Supervisor)
sudo supervisorctl restart hqms-worker:*
sudo supervisorctl restart hqms-reverb:*

# Set permissions
sudo chown -R deploy:www-data /var/www/hqms
sudo chmod -R 775 /var/www/hqms/storage
sudo chmod -R 775 /var/www/hqms/bootstrap/cache

# Disable maintenance mode
php artisan up

echo "Deployment completed successfully!"
```

Make executable:
```bash
chmod +x /var/www/hqms/deploy.sh
```

## Troubleshooting

### Permission Denied Errors

```bash
# Fix storage permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R deploy:www-data storage bootstrap/cache

# If SELinux is enabled (rare on Ubuntu)
sudo chcon -R -t httpd_sys_rw_content_t storage
sudo chcon -R -t httpd_sys_rw_content_t bootstrap/cache
```

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -100 /var/www/hqms/storage/logs/laravel.log

# Check Nginx error log
sudo tail -100 /var/log/nginx/error.log

# Ensure .env exists and APP_KEY is set
cat .env | grep APP_KEY
```

### Composer Memory Issues

```bash
# Increase PHP memory limit temporarily
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev
```

### Class Not Found After Deployment

```bash
# Regenerate autoloader
composer dump-autoload --optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Connection Issues

```bash
# Test MySQL connection
mysql -u hqms_user -p -h localhost hqms

# Check .env database settings
cat .env | grep DB_
```

## Zero-Downtime Deployment (Advanced)

For zero-downtime deployments, consider using:

1. **Laravel Envoyer** (paid service)
2. **Deployer** (free, PHP-based)
3. **GitHub Actions** with SSH

Example GitHub Actions workflow:

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/hqms
            ./deploy.sh
```

**Next**: [04-SUPERVISOR.md](./04-SUPERVISOR.md) - Configure queue workers and Reverb
