#!/usr/bin/env bash

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
ROOT="$(dirname $(dirname $DIR))"
DRONE_DIR=$(realpath --relative-to="$ROOT" "$DIR")

docker run --rm -w /app -u $UID -ti -v "$ROOT:/app" hairyhenderson/gomplate:alpine \
  -f "$DRONE_DIR/.drone.yml.tmpl" -o .drone.yml
