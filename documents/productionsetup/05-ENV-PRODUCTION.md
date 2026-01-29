# Production Environment Configuration

This document details all the `.env` changes required for production deployment.

## Complete Production .env Template

```bash
nano /var/www/hqms/.env
```

```env
#──────────────────────────────────────────────────────────────────────────────
# APPLICATION
#──────────────────────────────────────────────────────────────────────────────
APP_NAME="CareTime"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_TIMEZONE=Asia/Manila
APP_URL=https://your-domain.com
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

#──────────────────────────────────────────────────────────────────────────────
# DATABASE
#──────────────────────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hqms
DB_USERNAME=hqms_user
DB_PASSWORD=your_strong_database_password

#──────────────────────────────────────────────────────────────────────────────
# CACHE, SESSION, QUEUE
#──────────────────────────────────────────────────────────────────────────────
# Use Redis for better performance in production
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database

SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=your-domain.com
SESSION_SECURE_COOKIE=true

#──────────────────────────────────────────────────────────────────────────────
# REDIS
#──────────────────────────────────────────────────────────────────────────────
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

#──────────────────────────────────────────────────────────────────────────────
# BROADCASTING (Laravel Reverb)
#──────────────────────────────────────────────────────────────────────────────
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=hqms
REVERB_APP_KEY=your-reverb-app-key
REVERB_APP_SECRET=your-reverb-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Client-side Reverb configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=your-domain.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

#──────────────────────────────────────────────────────────────────────────────
# MAIL
#──────────────────────────────────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Alternative: Use SendGrid, Amazon SES, etc.
# MAIL_MAILER=ses
# AWS_ACCESS_KEY_ID=your-access-key
# AWS_SECRET_ACCESS_KEY=your-secret-key
# AWS_DEFAULT_REGION=us-east-1

#──────────────────────────────────────────────────────────────────────────────
# LOGGING
#──────────────────────────────────────────────────────────────────────────────
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

#──────────────────────────────────────────────────────────────────────────────
# PDF GENERATION (spatie/browsershot)
#──────────────────────────────────────────────────────────────────────────────
# Chromium path on Ubuntu
BROWSERSHOT_CHROME_PATH=/usr/bin/chromium-browser

# Or if using puppeteer
# BROWSERSHOT_NODE_PATH=/usr/bin/node
# BROWSERSHOT_NPM_PATH=/usr/bin/npm

#──────────────────────────────────────────────────────────────────────────────
# SANCTUM (API Authentication)
#──────────────────────────────────────────────────────────────────────────────
SANCTUM_STATEFUL_DOMAINS=your-domain.com,api.your-domain.com

#──────────────────────────────────────────────────────────────────────────────
# SECURITY
#──────────────────────────────────────────────────────────────────────────────
# Trusted proxies (DigitalOcean load balancers, Cloudflare, etc.)
TRUSTED_PROXIES=*

# Force HTTPS
FORCE_HTTPS=true

#──────────────────────────────────────────────────────────────────────────────
# HQMS SPECIFIC SETTINGS
#──────────────────────────────────────────────────────────────────────────────
# Clinic information
CLINIC_NAME="Guardiano Maternity and Children Clinic and Hospital"
CLINIC_ADDRESS="123 Main Street, City, Province"
CLINIC_PHONE="+63 123 456 7890"
CLINIC_EMAIL="info@your-domain.com"

# Operating hours
CLINIC_OPEN_TIME=08:00
CLINIC_CLOSE_TIME=17:00
```

## Key Differences: Local vs Production

| Setting | Local | Production |
|---------|-------|------------|
| `APP_ENV` | local | **production** |
| `APP_DEBUG` | true | **false** |
| `APP_URL` | http://hqms.test | **https://your-domain.com** |
| `DB_CONNECTION` | sqlite | **mysql** |
| `CACHE_STORE` | database | **redis** |
| `SESSION_DRIVER` | database | **redis** |
| `SESSION_SECURE_COOKIE` | false | **true** |
| `LOG_LEVEL` | debug | **error** |
| `VITE_REVERB_SCHEME` | http | **https** |
| `VITE_REVERB_PORT` | 8080 | **443** |

## Generate Secure Values

### Application Key

```bash
php artisan key:generate
```

### Reverb Keys

```bash
# Generate random keys
php -r "echo 'REVERB_APP_KEY=' . bin2hex(random_bytes(16)) . PHP_EOL;"
php -r "echo 'REVERB_APP_SECRET=' . bin2hex(random_bytes(32)) . PHP_EOL;"
```

### Strong Database Password

```bash
# Generate random password
openssl rand -base64 24
```

## Environment-Specific Configurations

### Reverb (WebSockets) Explanation

**Server-side (Supervisor runs Reverb):**
```env
REVERB_HOST=127.0.0.1    # Listen on localhost only
REVERB_PORT=8080          # Internal port
REVERB_SCHEME=http        # Internal connection is HTTP
```

**Client-side (Browser connects via Nginx):**
```env
VITE_REVERB_HOST=your-domain.com    # Public domain
VITE_REVERB_PORT=443                 # HTTPS port
VITE_REVERB_SCHEME=https             # Secure connection
```

Nginx proxies `/app` to `127.0.0.1:8080` internally.

### Redis Configuration

If Redis requires authentication:
```env
REDIS_PASSWORD=your-redis-password
```

To configure in `/etc/redis/redis.conf`:
```bash
sudo nano /etc/redis/redis.conf
# Add: requirepass your-redis-password
sudo systemctl restart redis-server
```

### Mail Configuration Options

**Mailgun:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.your-domain.com
MAIL_PASSWORD=your-mailgun-api-key
```

**SendGrid:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
```

**Amazon SES:**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
```

## After Changing .env

Always run these commands after modifying `.env`:

```bash
# Clear and re-cache configuration
php artisan config:clear
php artisan config:cache

# Restart queue workers to pick up new config
sudo supervisorctl restart hqms-worker:*
sudo supervisorctl restart hqms-reverb
```

## Security Best Practices

1. **Never commit `.env` to version control**
   ```bash
   # Verify .env is in .gitignore
   cat .gitignore | grep .env
   ```

2. **Restrict .env file permissions**
   ```bash
   chmod 600 /var/www/hqms/.env
   ```

3. **Use strong, unique passwords**
   - Database password: 24+ characters
   - Redis password: 32+ characters (if used)
   - API keys: Use official generated keys

4. **Rotate keys periodically**
   - Regenerate `APP_KEY` annually (will invalidate sessions)
   - Rotate `REVERB_APP_SECRET` periodically

## Debugging Production Issues

If you need temporary debugging:

```bash
# Enable debug mode temporarily
php artisan down
nano .env  # Set APP_DEBUG=true
php artisan config:clear
# Test and check logs
# Then disable immediately:
nano .env  # Set APP_DEBUG=false
php artisan config:cache
php artisan up
```

**WARNING**: Never leave `APP_DEBUG=true` in production. It exposes sensitive information.

**Next**: [06-SSL-SETUP.md](./06-SSL-SETUP.md) - Configure HTTPS with Let's Encrypt
