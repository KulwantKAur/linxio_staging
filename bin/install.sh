#!/bin/bash

infra_path=./devops

# env
if [ ! -f ./.env ]; then
  cp ./.env.dist ./.env
  echo "./.env has been created"
fi

# load environment variables from .env
if [ -f .env ]
then
  export $(cat .env | sed 's/#.*//g' | xargs)
fi

# nginx
if [ ! -f $infra_path/nginx/nginx.conf ]; then
  cp $infra_path/nginx/nginx.conf.tpl $infra_path/nginx/nginx.conf
  echo "$infra_path/nginx/nginx.conf has been created"
fi
if [ ! -f $infra_path/nginx/conf.d/default.conf ]; then
  cp $infra_path/nginx/conf.d/default.conf.tpl $infra_path/nginx/conf.d/default.conf
  echo "$infra_path/nginx/conf.d/default.conf has been created"
fi

# php
if [ ! -f $infra_path/php-fpm/Dockerfile ]; then
  cp $infra_path/php-fpm/Dockerfile.example $infra_path/php-fpm/Dockerfile
  echo "$infra_path/php-fpm/Dockerfile has been created"
fi
if [ ! -f $infra_path/php-fpm/config/www.conf ]; then
  cp $infra_path/php-fpm/config/www.conf.example $infra_path/php-fpm/config/www.conf
  echo "$infra_path/php-fpm/config/www.conf has been created"
fi

# supervisor
if [ ! -f $infra_path/supervisor/config/www.conf ]; then
  cp $infra_path/supervisor/config/www.conf.example $infra_path/supervisor/config/www.conf
  echo "$infra_path/supervisor/config/www.conf has been created"
fi
if [ ! -f $infra_path/supervisor/supervisor/prod.conf ]; then
  cp $infra_path/supervisor/supervisor/prod.conf.example $infra_path/supervisor/supervisor/prod.conf
  echo "$infra_path/supervisor/supervisor/prod.conf has been created"
fi

# traccar
if [ ! -f $infra_path/traccar/traccar.xml ]; then
  cp $infra_path/traccar/traccar.xml.dist $infra_path/traccar/traccar.xml
  echo "$infra_path/traccar/traccar.xml has been created"
fi
PGPASSWORD=$POSTGRES_PASSWORD psql -U $POSTGRES_USER -p $DB_PORT -h $DB_HOST -w -c 'create database '$TRACCAR_DATABASE_NAME';'
echo "database '$TRACCAR_DATABASE_NAME' has been created"
PGPASSWORD=$POSTGRES_PASSWORD psql -U $POSTGRES_USER -p $DB_PORT -h $DB_HOST -w -c 'grant all privileges on database '$TRACCAR_DATABASE_NAME' to '$POSTGRES_USER
echo "grant all privileges on database '$TRACCAR_DATABASE_NAME' to user '$POSTGRES_USER' has been created"

# centrifugo
if [ ! -f $infra_path/centrifugo/config.json ]; then
  cp $infra_path/centrifugo/config.json.example $infra_path/centrifugo/config.json
  echo "$infra_path/centrifugo/config.json has been created"
fi

# firebase (FCM)
if [ ! -f ./app/config/firebase.json ]; then
  cp ./app/config/firebase.json.dist ./app/config/firebase.json
  echo "./app/config/firebase.json has been created"
fi

# docker-compose.yml
if [ ! -f ./docker-compose.yml ]; then
  cp ./docker-compose.example.yml ./docker-compose.yml
  echo "./docker-compose.yml has been created"
fi