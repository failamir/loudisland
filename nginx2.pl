# Reverse proxy (80/443) -> backend Laravel di :8080
server {
  listen 80;
  listen [::]:80;
  listen 443 ssl http2;
  listen [::]:443 ssl http2;

  server_name mandalikakorprirun.com;

  # SSL (isi cert/key Anda)
  ssl_certificate     /etc/letsencrypt/live/mandalikakorprirun.com/fullchain.pem;
  ssl_certificate_key /etc/letsencrypt/live/mandalikakorprirun.com/privkey.pem;

  # Root tetap mengarah ke public agar static assets (css/js/png…) bisa dilayani langsung jika perlu
  root /var/www/loudisland/public;
  index index.php index.html;

  # Redirect HTTP -> HTTPS
  if ($scheme != "https") {
    return 301 https://$host$request_uri;
  }

  # Let's Encrypt
  location ~ /.well-known {
    auth_basic off;
    allow all;
  }

  # Static assets (dilayani langsung)
  location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|gz|svg|svgz|ttf|otf|woff|woff2|eot|mp4|ogg|ogv|webm|webp|zip|swf|map)$ {
    add_header Access-Control-Allow-Origin "*";
    expires max;
    access_log off;
    try_files $uri =404;
  }

  # Khusus /docs dan /openapi.yaml pastikan lewat ke backend Laravel
  location = /docs {
    proxy_pass http://127.0.0.1:8080;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
  }
  location = /openapi.yaml {
    # Bisa dilayani langsung dari public, tapi aman juga diteruskan ke backend
    proxy_pass http://127.0.0.1:8080;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
  }

  # Sisanya proxy ke backend
  location / {
    proxy_pass http://127.0.0.1:8080;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_redirect off;
    proxy_read_timeout 720s;
    proxy_send_timeout 720s;
  }
}

# Backend Laravel (PHP-FPM)
server {
  listen 8080;
  listen [::]:8080;
  server_name mandalikakorprirun.com;

  root /var/www/loudisland/public;
  index index.php index.html;

  # Laravel front controller
  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  # PHP-FPM
  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_intercept_errors on;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_read_timeout 3600;
    fastcgi_send_timeout 3600;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;  # ganti sesuai sock/port PHP-FPM Anda
  }

  # Static assets dari public/
  location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|gz|svg|svgz|ttf|otf|woff|woff2|eot|mp4|ogg|ogv|webm|webp|zip|swf|map)$ {
    add_header Access-Control-Allow-Origin "*";
    expires max;
    access_log off;
    try_files $uri =404;
  }
}