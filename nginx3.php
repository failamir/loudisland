# Front: HTTPS reverse proxy to backend
server {
listen 80;
listen [::]:80;
listen 443 ssl http2;
listen [::]:443 ssl http2;

server_name mandalikakorprirun.com;

# SSL certs
#ssl_certificate /etc/letsencrypt/live/mandalikakorprirun.com/fullchain.pem;
#ssl_certificate_key /etc/letsencrypt/live/mandalikakorprirun.com/privkey.pem;

# Force HTTPS
if ($scheme != "https") {
return 301 https://$host$request_uri;
}

# Allow ACME challenge
location ~ /.well-known {
auth_basic off;
allow all;
root /var/www/letsencrypt; # optional; sesuaikan jika pakai certbot webroot
}

# Proxy all requests to backend
location / {
proxy_pass http://127.0.0.1:8080;
proxy_set_header Host $http_host;
proxy_set_header X-Forwarded-Host $http_host;
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_read_timeout 720s;
}
}

# Backend: Laravel on 8080
server {
listen 8080;
listen [::]:8080;
server_name mandalikakorprirun.com;

root /home/ifailamir-korpri/htdocs/mandalikakorprirun.com/public;
index index.php index.html;

# Laravel routes
location / {
try_files $uri $uri/ /index.php?$query_string;
}

# PHP handling
location ~ \.php$ {
include fastcgi_params;
fastcgi_index index.php;
fastcgi_intercept_errors on;

# CHOOSE ONE of these depending on your PHP-FPM setup:
# fastcgi_pass unix:/run/php/php8.2-fpm.sock; # socket (common on Debian/Ubuntu)
fastcgi_pass 127.0.0.1:9000; # TCP (if your PHP-FPM listens here)

fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
fastcgi_param DOCUMENT_ROOT $realpath_root;
fastcgi_read_timeout 3600;
fastcgi_send_timeout 3600;
}

# Static files
location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|gz|svg|svgz|ttf|otf|woff|woff2|eot|mp4|ogg|ogv|webm|webp|zip|swf|map)$ {
add_header Access-Control-Allow-Origin "*";
expires max;
access_log off;
try_files $uri @laravel; # if asset not found, pass to Laravel
}

location @laravel {
rewrite ^/(.*)$ /index.php?$1 last;
}
}