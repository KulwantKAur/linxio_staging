user  nginx;
worker_processes  auto;

error_log  /var/log/nginx/error.log error;
# error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;


events {
    # increase number if you need to support more connections simultaneously
    worker_connections  16384;
}


http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    # access_log  /var/log/nginx/access.log  main;
    access_log  off;

    limit_req_zone $binary_remote_addr zone=tracker-teltonika:10m rate=15r/s;
    limit_req_zone $binary_remote_addr zone=tracker-ulbotech:10m rate=5r/s;
    limit_req_zone $binary_remote_addr zone=tracker-topflytech:10m rate=25r/s;
    limit_req_zone $binary_remote_addr zone=tracker-pivotel:10m rate=25r/s;
    limit_req_zone $binary_remote_addr zone=trackers:10m rate=50r/s;

    sendfile        on;
    # tcp_nopush     on;
    # tcp_nodelay    on;
    # multi_accept on; # need to test on working server

    keepalive_timeout  65;
    
    #gzip  on;

    include /etc/nginx/conf.d/*.conf;
}
