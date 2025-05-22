#!/usr/bin/env bash

set -euo pipefail

if [ -n "${AWS_ENV_PATH-}" ]; then
  eval "$(aws-env)"
fi