#!/usr/bin/env bash

set -euo pipefail

if [ -n "${AWS_ENV_PATH}" ]; then
  eval "$(aws-env)"
fi

if [[ -n "${JWT_PUBLIC_KEY}" ]]; then
  echo "$JWT_PUBLIC_KEY" | base64 -d > public.pem
fi

if [[ -n "${JWT_PUBLIC_KEY}" ]]; then
  echo "$JWT_PUBLIC_KEY" | base64 -d > env
fi

centrifugo