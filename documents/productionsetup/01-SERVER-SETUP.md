# Server Setup - DigitalOcean Droplet

## Server Details

| Item | Value |
|------|-------|
| **Server IP** | `146.190.100.242` |
| **Hostname** | `caretime-server` |
| **Application** | CareTime (HQMS) |

## Create Droplet

1. Log in to DigitalOcean
2. Create Droplet:
   - **Image**: Ubuntu 22.04/24.04 (LTS) x64
   - **Plan**: Basic, Regular Intel, $24/mo (4GB RAM, 2 vCPUs)
   - **Region**: Singapore (closest to Philippines)
   - **Authentication**: SSH Key (recommended)
   - **Hostname**: caretime-server

## Initial Server Configuration

### 1. Connect to Server

```bash
ssh root@your_server_ip
```

### 2. Update System

```bash
apt update && apt upgrade -y
```

### 3. Create Deploy User

```bash
# Create user
adduser deploy
# Add to sudo group
usermod -aG sudo deploy
# Add to www-data group (for Nginx)
usermod -aG www-data deploy
```

### 4. Configure SSH for Deploy User

```bash
# Copy SSH keys to deploy user
rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy

# Test login (from your local machine)
# ssh deploy@your_server_ip
```

### 5. Secure SSH (Optional but Recommended)

```bash
sudo nano /etc/ssh/sshd_config
```

Change these settings:
```
PermitRootLogin no
PasswordAuthentication no
```

Restart SSH:
```bash
sudo systemctl restart sshd
```

### 6. Configure Firewall (UFW)

```bash
# Enable UFW
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw allow 8080       # For Laravel Reverb WebSockets
sudo ufw enable

# Verify
sudo ufw status
```

Expected output:
```
Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
Nginx Full                 ALLOW       Anywhere
8080                       ALLOW       Anywhere
```

### 7. Install Fail2ban (Optional but Recommended)

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

## Install Required Software

### 1. Add PHP 8.4 Repository

```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### 2. Install PHP 8.4 and Extensions

```bash
sudo apt install php8.4-fpm php8.4-cli php8.4-common php8.4-mysql \
    php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd \
    php8.4-redis php8.4-bcmath php8.4-intl php8.4-readline -y
```

Verify installation:
```bash
php -v
# Should show PHP 8.4.x
```

### 3. Install Nginx

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 4. Install MySQL 8.0

```bash
sudo apt install mysql-server -y
sudo systemctl enable mysql
sudo systemctl start mysql

# Secure installation
sudo mysql_secure_installation
```

Create database and user:
```bash
sudo mysql
```

```sql
CREATE DATABASE hqms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hqms_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON hqms.* TO 'hqms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Install Redis

```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Verify
redis-cli ping
# Should return: PONG
```

### 6. Install Supervisor

```bash
sudo apt install supervisor -y
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

### 7. Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Verify
composer --version
```

### 8. Install Node.js 20.x

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Verify
node -v
npm -v
```

### 9. Install Chromium (for PDF Generation)

```bash
sudo apt install chromium-browser -y

# Verify
chromium-browser --version
```

### 10. Install Git

```bash
sudo apt install git -y
```

## Configure PHP-FPM

Edit PHP-FPM pool configuration:

```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

Recommended settings for 4GB RAM:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Edit PHP settings:
```bash
sudo nano /etc/php/8.4/fpm/php.ini
```

Production settings:
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 512M
max_execution_time = 300
display_errors = Off
log_errors = On
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.4-fpm
```

## Create Application Directory

```bash
sudo mkdir -p /var/www/hqms
sudo chown -R deploy:www-data /var/www/hqms
sudo chmod -R 755 /var/www
```

## Summary

At this point you should have:
- [x] Deploy user created
- [x] SSH secured
- [x] Firewall configured
- [x] PHP 8.4 with FPM installed
- [x] Nginx installed
- [x] MySQL 8.0 with database created
- [x] Redis installed
- [x] Supervisor installed
- [x] Composer installed
- [x] Node.js installed
- [x] Chromium installed for PDF generation
- [x] Git installed

**Next**: [02-NGINX-CONFIG.md](./02-NGINX-CONFIG.md) - Configure Nginx for Laravel
