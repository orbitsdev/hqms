# Nginx Configuration

## Server Details

| Item | Value |
|------|-------|
| **Server IP** | `146.190.100.242` |
| **Application** | CareTime (HQMS) |
| **Document Root** | `/var/www/hqms/public` |

## Main Site Configuration

Create Nginx configuration for CareTime:

```bash
sudo nano /etc/nginx/sites-available/hqms
```

Paste the following configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 146.190.100.242;  # Replace with domain when ready
    root /var/www/hqms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Favicon and robots
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Error page
    error_page 404 /index.php;

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Timeouts for long-running requests (PDF generation)
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml application/atom+xml image/svg+xml;

    # Client body size (for file uploads)
    client_max_body_size 50M;
}
```

## Laravel Reverb (WebSockets) Configuration

If you're running Reverb on the same server, add this upstream block and location:

```bash
sudo nano /etc/nginx/sites-available/hqms
```

Add at the **top** of the file (before the server block):

```nginx
# Upstream for Laravel Reverb WebSocket
upstream reverb {
    server 127.0.0.1:8080;
}
```

Add inside the server block:

```nginx
    # Laravel Reverb WebSocket proxy
    location /app {
        proxy_pass http://reverb;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # WebSocket timeouts
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
    }
```

## Complete Configuration Example (Copy-Paste Ready)

Here's the complete Nginx configuration with Reverb support for **CareTime** server `146.190.100.242`:

```bash
sudo nano /etc/nginx/sites-available/hqms
```

```nginx
# Upstream for Laravel Reverb WebSocket
upstream reverb {
    server 127.0.0.1:8080;
}

server {
    listen 80;
    listen [::]:80;
    server_name 146.190.100.242;
    root /var/www/hqms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;
    client_max_body_size 120M;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Laravel Reverb WebSocket proxy
    location /app {
        proxy_pass http://reverb;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
    }

    # Favicon and robots
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Error page
    error_page 404 /index.php;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    # Deny hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Enable the Site

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/hqms /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# If test passes, reload Nginx
sudo systemctl reload nginx
```

## Verify Configuration

```bash
# Check Nginx status
sudo systemctl status nginx

# Check error logs if issues
sudo tail -f /var/log/nginx/error.log

# Check access logs
sudo tail -f /var/log/nginx/access.log
```

## Nginx Performance Tuning

Edit the main Nginx configuration:

```bash
sudo nano /etc/nginx/nginx.conf
```

Recommended settings for 4GB RAM server:

```nginx
user www-data;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
    worker_connections 2048;
    multi_accept on;
    use epoll;
}

http {
    # Basic Settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;

    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # Logging
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Gzip (global)
    gzip on;

    # Virtual Host Configs
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
```

Restart Nginx:
```bash
sudo systemctl restart nginx
```

## Queue Display (TV Monitor) Subdomain (Optional)

If you want a separate subdomain for the queue display:

```bash
sudo nano /etc/nginx/sites-available/hqms-display
```

```nginx
server {
    listen 80;
    server_name display.your-domain.com;
    root /var/www/hqms/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable:
```bash
sudo ln -s /etc/nginx/sites-available/hqms-display /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

**Next**: [03-LARAVEL-DEPLOYMENT.md](./03-LARAVEL-DEPLOYMENT.md) - Deploy the Laravel application
