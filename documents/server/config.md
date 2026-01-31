# Nginx Config - CareTime Server (146.190.100.242)

Copy this to `/etc/nginx/sites-available/hqms`:

```bash
sudo nano /etc/nginx/sites-available/hqms
```

```nginx
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

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

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
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## After saving, run:

```bash
# Enable site (if not already)
sudo ln -sf /etc/nginx/sites-available/hqms /etc/nginx/sites-enabled/hqms

# Remove default
sudo rm -f /etc/nginx/sites-enabled/default

# Test config
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```
