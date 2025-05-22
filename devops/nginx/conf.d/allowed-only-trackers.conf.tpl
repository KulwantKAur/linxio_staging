server {
    listen 444;
    port_in_redirect off;
    return         301 http://$host:8081/api/short-urls$request_uri;
}

server {
    listen 80;
    listen 8080;
    server_name _;
    proxy_connect_timeout 300s;
    proxy_read_timeout 300s;
    gzip             on;
    gzip_comp_level  2;
    gzip_min_length  1000;
    gzip_proxied     expired no-cache no-store private auth;
    gzip_types       text/plain application/x-javascript application/javascript text/xml text/css application/xml;
    client_max_body_size        100M;

    location / {
        root /srv/web;
        try_files /maintenance.html =404;
    }
    location /uploads/ {
        root /srv/web/;
    }
    location ~* ^/api/(tracker|traccar|streamax)/.* {
        limit_req zone=trackers burst=10 nodelay;
        set $api_root /srv/public;
        set $api_entrypoint index.php;
        fastcgi_pass php:9000;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $api_root/$api_entrypoint;
        fastcgi_param SCRIPT_NAME $api_entrypoint;
        fastcgi_read_timeout 600;
    }
}