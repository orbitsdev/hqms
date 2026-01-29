# SSL/HTTPS Setup with Let's Encrypt

## Prerequisites

- Domain name pointing to your server's IP address
- DNS propagation complete (can take up to 48 hours)
- Nginx configured and running
- Port 80 and 443 open in firewall

## Verify DNS

```bash
# Check if domain resolves to your server
dig +short your-domain.com
# Should return your server's IP address

# Or use nslookup
nslookup your-domain.com
```

## Install Certbot

```bash
# Install Certbot and Nginx plugin
sudo apt install certbot python3-certbot-nginx -y
```

## Obtain SSL Certificate

```bash
# Run Certbot with Nginx plugin
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

You'll be prompted to:
1. Enter email address (for renewal notifications)
2. Agree to terms of service
3. Optionally share email with EFF
4. Choose whether to redirect HTTP to HTTPS (recommended: option 2)

## What Certbot Does

Certbot automatically:
1. Obtains SSL certificate from Let's Encrypt
2. Modifies your Nginx configuration to use SSL
3. Sets up HTTP to HTTPS redirect
4. Configures certificate auto-renewal

## Verify SSL Configuration

After Certbot runs, your Nginx config should look like this:

```bash
sudo cat /etc/nginx/sites-available/hqms
```

```nginx
upstream reverb {
    server 127.0.0.1:8080;
}

server {
    server_name your-domain.com www.your-domain.com;
    root /var/www/hqms/public;

    # ... (rest of your config)

    listen [::]:443 ssl ipv6only=on; # managed by Certbot
    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
}

server {
    if ($host = www.your-domain.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot

    if ($host = your-domain.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot

    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 404; # managed by Certbot
}
```

## Enhanced SSL Configuration (A+ Rating)

For better security, create a custom SSL parameters file:

```bash
sudo nano /etc/nginx/snippets/ssl-params.conf
```

```nginx
# SSL Configuration for A+ rating on SSL Labs

# Protocols - only TLS 1.2 and 1.3
ssl_protocols TLSv1.2 TLSv1.3;

# Cipher suites
ssl_prefer_server_ciphers on;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;

# OCSP Stapling
ssl_stapling on;
ssl_stapling_verify on;
resolver 8.8.8.8 8.8.4.4 valid=300s;
resolver_timeout 5s;

# Security Headers
add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Session
ssl_session_timeout 1d;
ssl_session_cache shared:SSL:50m;
ssl_session_tickets off;
```

Include in your site config:
```nginx
server {
    listen 443 ssl http2;
    # ...

    include /etc/nginx/snippets/ssl-params.conf;
}
```

## Test SSL Configuration

```bash
# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx

# Test HTTPS in browser
# Visit: https://your-domain.com

# Test SSL rating
# Visit: https://www.ssllabs.com/ssltest/analyze.html?d=your-domain.com
```

## Automatic Certificate Renewal

Let's Encrypt certificates expire every 90 days. Certbot sets up auto-renewal.

### Verify Auto-Renewal Timer

```bash
# Check systemd timer
sudo systemctl status certbot.timer

# List timers
sudo systemctl list-timers | grep certbot
```

### Test Renewal Process

```bash
# Dry run (doesn't actually renew)
sudo certbot renew --dry-run
```

### Manual Renewal (if needed)

```bash
sudo certbot renew
sudo systemctl reload nginx
```

### Custom Renewal Hook

To restart services after renewal:

```bash
sudo nano /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
```

```bash
#!/bin/bash
systemctl reload nginx
supervisorctl restart hqms-reverb
```

```bash
sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
```

## Update Laravel Configuration

After SSL is set up, update your `.env`:

```env
APP_URL=https://your-domain.com

# Session security
SESSION_SECURE_COOKIE=true

# Reverb client config (for browsers)
VITE_REVERB_SCHEME=https
VITE_REVERB_PORT=443
```

Rebuild assets and clear cache:
```bash
cd /var/www/hqms
npm run build
php artisan config:cache
```

## Force HTTPS in Laravel

Add to `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

Or add middleware in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    if (app()->environment('production')) {
        $middleware->trustProxies(at: '*');
    }
})
```

## WebSocket (Reverb) over HTTPS

With Nginx proxying, WebSocket connections use the same SSL certificate.

**Browser connects to:**
```
wss://your-domain.com/app/your-app-key
```

**Nginx proxies to internal Reverb:**
```
ws://127.0.0.1:8080/app/your-app-key
```

This is already configured if you followed [02-NGINX-CONFIG.md](./02-NGINX-CONFIG.md).

## Multiple Domains/Subdomains

### Add Subdomain for Queue Display

```bash
# Obtain certificate for subdomain
sudo certbot --nginx -d display.your-domain.com
```

### Wildcard Certificate (Advanced)

For multiple subdomains:

```bash
# Requires DNS challenge
sudo certbot certonly --manual --preferred-challenges dns -d your-domain.com -d *.your-domain.com
```

## Troubleshooting

### Certificate Not Found

```bash
# List certificates
sudo certbot certificates

# Check certificate location
ls -la /etc/letsencrypt/live/
```

### Renewal Failed

```bash
# Check renewal log
sudo cat /var/log/letsencrypt/letsencrypt.log

# Common issue: port 80 blocked
sudo ufw allow 80
sudo certbot renew
```

### Mixed Content Warnings

If browser shows mixed content:
1. Check all URLs in code use `https://` or relative paths
2. Check `.env` has `APP_URL=https://...`
3. Clear browser cache
4. Run `php artisan config:cache`

### WebSocket Connection Failed

```bash
# Check if Reverb is running
sudo supervisorctl status hqms-reverb

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Test internal WebSocket
curl -i http://127.0.0.1:8080
```

## Certificate Backup

```bash
# Backup certificates
sudo tar -czvf /home/deploy/letsencrypt-backup.tar.gz /etc/letsencrypt

# Store securely off-server
```

**Next**: [07-MAINTENANCE.md](./07-MAINTENANCE.md) - Maintenance, backups, and monitoring
