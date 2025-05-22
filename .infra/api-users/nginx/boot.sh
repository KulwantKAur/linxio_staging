#!/usr/bin/env bash

set -ef

main_conf_path="/etc/nginx"
main_conf_file="nginx.conf.tpl"

cp "$main_conf_path/$main_conf_file" "/usr/local/openresty/nginx/conf/nginx.conf"

dockerize -template /etc/nginx/conf.d/default.conf.tpl:/etc/nginx/conf.d/default.conf \
  -wait "tcp://${NGINX_PHP_FPM_DSN:-php-fpm-users:9000}" \
  -timeout 5m -wait-retry-interval 10s \
  /usr/local/openresty/bin/openresty -g 'daemon off;'
