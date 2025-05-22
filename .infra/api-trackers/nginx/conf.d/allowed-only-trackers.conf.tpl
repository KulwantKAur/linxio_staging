{{ define "cors" }}
        more_set_headers
          'Access-Control-Allow-Origin: {{ default .Env.NGINX_CORS_ALLOW_ORIGIN "*" }}'
          'Access-Control-Allow-Credentials: true'
          'Access-Control-Allow-Headers: Authorization,Accept,Origin,DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range'
          'Access-Control-Allow-Methods: GET,POST,OPTIONS,PUT,DELETE,PATCH'
        ;

        if ($request_method = 'OPTIONS') {
          more_set_headers
            'Access-Control-Max-Age: 1728000'
            'Content-Type: text/plain charset=UTF-8'
            'Content-Length: 0'
          ;

          return 204;
        }
{{ end -}}

{{ $PHP_FPM_DSN := default .Env.NGINX_PHP_FPM_DSN "php-fpm-users:9000" -}}
server {
    listen 444;
    port_in_redirect off;
    return 301 http://$host:8081/api/short-urls$request_uri;
}

log_format json_combined escape=json
   '{'
     '"channel":"nginx",'
     '"datetime":"$time_iso8601",'
     '"extra":{'
       '"req":{'
         '"bytes":$body_bytes_sent,'
         '"ip":"$http_x_forwarded_for",'
         '"host":"$host",'
         '"ref":"$http_referer",'
         '"authorization":"$http_authorization",'
         '"agent":"$http_user_agent",'
         '"method":"$request_method",'
         '"protocol":"$server_protocol",'
         '"rm_addr":"$remote_addr",'
         '"rm_user":"$remote_user",'
         '"id":"$req_id",'
         '"time":$request_time,'
         '"status":$status,'
         '"uri":"$uri"'
       '}'
     '},'
     '"level":$level,'
     '"level_name":"$levelLabel",'
     '"message":"-"'
   '}';

map $http_x_request_id $req_id {
    default   $http_x_request_id;
    ""        $request_id;
}

map $status $levelLabel {
    ~^[23]  "DEBUG";
    ~^[5]   "ERROR";
    default "INFO";
}

map $status $level {
    ~^[23]  "100";
    ~^[5]   "400";
    default "200";
}

map $request_uri $loggable_uri {
  /api/health 0;
  default     1;
}

split_clients $request_id $loggable_on_success {
    1% $loggable_uri;
    *  0;
}

map $status $loggable {
    ~^[23]  $loggable_on_success;
    default 1;
}

server {
    listen 80;
    listen 8080;
    server_name _;
    proxy_connect_timeout 300s;
    proxy_read_timeout 300s;
    set_real_ip_from 10.0.0.0/16;
    real_ip_header X-Forwarded-For;
    gzip on;
    gzip_comp_level 2;
    gzip_min_length 1000;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain application/x-javascript application/javascript text/xml text/css application/xml;
    client_max_body_size 100M;

    access_log /usr/local/openresty/nginx/logs/access.log json_combined if=$loggable;

    location /uploads/ {
        {{- template "cors" . }}
        root /srv/public/;
    }

    location / {
        root /srv/web;
        try_files /maintenance.html =404;
    }
    
    location ~* ^/api/(tracker|traccar|streamax)/.* {
        {{- template "cors" . }}

        limit_req zone=trackers burst=10 nodelay;
        set $api_root /srv/public;
        set $api_entrypoint index.php;
        fastcgi_pass {{ $PHP_FPM_DSN }};
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $api_root/$api_entrypoint;
        fastcgi_param SCRIPT_NAME $api_entrypoint;
        fastcgi_param HTTP_X_REQUEST_ID $req_id;
        fastcgi_read_timeout 600;
        fastcgi_param REMOTE_ADDR $http_x_forwarded_for;
    }
}