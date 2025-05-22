#!/usr/bin/env bash

set -ef

conf="/etc/nginx/conf.d"
main_conf_path="/etc/nginx"
main_conf_file="nginx.conf.tpl"
ssl_cert="/certs/ssl.crt/server.crt"

if [ ! -f $conf/default.conf ]; then
  cp "$conf/default.conf.tpl" "$conf/default.conf"
fi

if [ -f $ssl_cert ]; then
  if [ ! -f $conf/default-ssl.conf ]; then
    cp "$conf/default-ssl.conf.tpl" "$conf/default-ssl.conf"
  fi

  cp "$conf/default-ssl.conf" "$conf/default.conf"
fi

if [ ! -f $main_conf_path/nginx.conf ]; then
  cp "$main_conf_path/$main_conf_file" "$main_conf_path/nginx.conf"
fi

nginx -g 'daemon off;'
