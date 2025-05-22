#!/usr/bin/env bash

set -euo pipefail

sed -ie 's/;log_limit = .*/log_limit = 8192/g' /usr/local/etc/php-fpm.conf

php bin/console cache:warmup --env="$SYMFONY_ENV"

dockerize -timeout 1m -wait-retry-interval 10s \
  -template /usr/local/etc/php-fpm.d/www.conf.tpl:/usr/local/etc/php-fpm.d/www.conf \
  php-fpm --allow-to-run-as-root

