#!/usr/bin/env bash

set -euo pipefail

if [ -n "${AWS_ENV_PATH-}" ]; then
  eval "$(aws-env)"
fi

mkdir -p app/config/jwt

if [[ -n "${JWT_PRIVATE_KEY-}" && ! -e app/config/jwt/private.pem ]]; then
  echo "$JWT_PRIVATE_KEY" | base64 -d > app/config/jwt/private.pem
fi

if [[ -n "${JWT_PUBLIC_KEY-}" && ! -e app/config/jwt/public.pem ]]; then
  echo "$JWT_PUBLIC_KEY" | base64 -d > app/config/jwt/public.pem
fi

mkdir -p app/config/sftp

if [[ -n "${SFTP_PRIVATE_KEY-}" && ! -e app/config/sftp/linxio-sftp.pem ]]; then
  echo "$SFTP_PRIVATE_KEY" | base64 -d > app/config/sftp/linxio-sftp.pem
fi

touch var/logs/prod.log

/usr/local/bin/critical.sh &

exec "$@"
