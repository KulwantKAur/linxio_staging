#!/bin/bash

set -e

# Perform all actions as $POSTGRES_USER
export PGUSER="$POSTGRES_USER"

POSTGIS_VERSION="${POSTGIS_VERSION%%+*}"

# Load PostGIS into both template_database and $POSTGRES_DB
for DB in example_test "$POSTGRES_DB" "${@}"; do
    echo "Updating PostGIS extensions '$DB' to $POSTGIS_VERSION"
    psql --dbname="$DB" -c "
        -- Upgrade PostGIS (includes raster)
        CREATE EXTENSION IF NOT EXISTS postgis VERSION '$POSTGIS_VERSION';
        ALTER EXTENSION postgis  UPDATE TO '$POSTGIS_VERSION';
    "
done
