server
{
  listen 80;
  listen 443 ssl http2;
  server_name mandalikakorprirun.com;
  index index.php index.html index.htm;
  root /www/wwwroot/mandalikakorprirun.com/public;

  #SSL-START SSL related configuration
  ssl_certificate  /www/server/panel/vhost/cert/mandalikakorprirun.com/fullchain.pem;
  ssl_certificate_key  /www/server/panel/vhost/cert/mandalikakorprirun.com/privkey.pem;
  ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
  ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
  ssl_prefer_server_ciphers on;
  ssl_session_tickets on;
  ssl_session_cache shared:SSL:10m;
  ssl_session_timeout 10m;
  add_header Strict-Transport-Security "max-age=31536000";
  error_page 497 https://$host$request_uri;
 
  # Redirect HTTP to HTTPS
  if ($scheme != "https") {
    rewrite ^ https://$host$uri permanent;
  }

  # The core Laravel routing logic
  location / {
    try_files $uri $uri/ /index.php?$args;
  }

  # PHP-FPM configuration
  location ~ \.php$ {
    include enable-php-80.conf;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
  }

  # Block access to sensitive files
  location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md)
  {
    return 404;
  }

  # Expose .well-known for SSL verification
  location ~ \.well-known {
    auth_basic off;
    allow all;
  }

  # Caching for static files
  location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|gz|svg|svgz|ttf|otf|woff|woff2|eot|mp4|ogg|ogv|webm|webp|zip|swf|map)$ {
    add_header Access-Control-Allow-Origin "*";
    expires max;
    access_log off;
  }
  
  access_log /www/wwwlogs/mandalikakorprirun.com.log;
  error_log /www/wwwlogs/mandalikakorprirun.com.error.log;
}