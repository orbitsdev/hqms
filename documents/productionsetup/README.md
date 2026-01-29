# Production Deployment Guide

Hospital Queue Management System (HQMS) - Production Setup for DigitalOcean with Nginx

## Stack Overview

| Component | Technology |
|-----------|------------|
| Server | DigitalOcean Droplet (Ubuntu 22.04 LTS) |
| Web Server | Nginx |
| PHP | PHP 8.4 with PHP-FPM |
| Database | MySQL 8.0 |
| Cache/Session | Redis |
| Queue Worker | Supervisor |
| WebSockets | Laravel Reverb (via Supervisor) |
| SSL | Let's Encrypt (Certbot) |
| PDF Generation | Chromium (for spatie/laravel-pdf) |

## Documentation Structure

1. **[01-SERVER-SETUP.md](./01-SERVER-SETUP.md)** - Initial DigitalOcean droplet setup
2. **[02-NGINX-CONFIG.md](./02-NGINX-CONFIG.md)** - Nginx configuration for Laravel
3. **[03-LARAVEL-DEPLOYMENT.md](./03-LARAVEL-DEPLOYMENT.md)** - Deploy the application
4. **[04-SUPERVISOR.md](./04-SUPERVISOR.md)** - Queue workers and Reverb setup
5. **[05-ENV-PRODUCTION.md](./05-ENV-PRODUCTION.md)** - Production environment variables
6. **[06-SSL-SETUP.md](./06-SSL-SETUP.md)** - HTTPS with Let's Encrypt
7. **[07-MAINTENANCE.md](./07-MAINTENANCE.md)** - Updates, backups, and monitoring

## Quick Start Checklist

```bash
# On your DigitalOcean droplet:

# 1. Initial server setup
sudo apt update && sudo apt upgrade -y
sudo adduser deploy
sudo usermod -aG sudo deploy

# 2. Install stack
sudo apt install nginx mysql-server redis-server supervisor -y
sudo apt install php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
    php8.4-xml php8.4-curl php8.4-zip php8.4-gd php8.4-redis php8.4-bcmath -y

# 3. Install Chromium for PDF generation
sudo apt install chromium-browser -y

# 4. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 5. Install Node.js (for asset building)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# 6. Clone and deploy application
cd /var/www
sudo git clone <repository-url> hqms
sudo chown -R deploy:www-data hqms
cd hqms

# 7. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 8. Configure environment
cp .env.example .env
php artisan key:generate
# Edit .env with production values

# 9. Run migrations
php artisan migrate --force

# 10. Set permissions
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 11. Configure Nginx, Supervisor, SSL
# (See individual documentation files)

# 12. Final optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

## Minimum Server Requirements

- **CPU**: 2 vCPUs
- **RAM**: 4 GB (for Chromium PDF generation)
- **Storage**: 50 GB SSD
- **OS**: Ubuntu 22.04 LTS

## Recommended DigitalOcean Droplet

**Basic Plan**: $24/month
- 2 vCPUs
- 4 GB RAM
- 80 GB SSD
- 4 TB Transfer

## Domain Setup

1. Point your domain's A record to the droplet's IP address
2. Wait for DNS propagation (can take up to 48 hours)
3. Configure Nginx with your domain
4. Install SSL certificate with Certbot

## Security Considerations

- [ ] SSH key authentication only (disable password login)
- [ ] UFW firewall enabled (allow 22, 80, 443, 8080 for Reverb)
- [ ] Fail2ban installed
- [ ] Regular security updates
- [ ] Database not exposed publicly
- [ ] `.env` file protected (never in version control)
- [ ] `APP_DEBUG=false` in production
