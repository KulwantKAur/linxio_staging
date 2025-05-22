#!/usr/bin/env bash

# Disable xdebug in production environment
xdebug_config=/usr/local/etc/php/conf.d/xdebug.ini
if [ -f $xdebug_config ] && [ "$SYMFONY_ENV" == "prod" ]
    then
        rm $xdebug_config
fi

# Wait for postgres to start

host=${SYMFONY__DATABASE__HOST}
port=${DB_PORT_INSIDE}

echo -n "waiting for TCP connection to database:..."

while ! nc -z -w 1 $host $port 2>/dev/null
do
  echo -n "."
  sleep 1
done

echo 'ok'

# Prepare application
COMPOSER_MEMORY_LIMIT=2G composer install
php bin/console cache:clear
supervisord
php-fpm --allow-to-run-as-root
