#!/bin/bash

psql=(psql --username "$POSTGRES_USER" --no-password)

# Create test DB as well
"${psql[@]}" --dbname postgres -c "CREATE DATABASE "$POSTGRES_DB"_test"

# Load PostGIS into both "$POSTGRES_DB"_test and $POSTGRES_DB
for DB in  "$POSTGRES_DB"_test "$POSTGRES_DB"; do
	echo "Loading PostGIS extensions into $DB"
    "${psql[@]}" --dbname="$DB" <<-'EOSQL'
        CREATE EXTENSION IF NOT EXISTS postgis;
        CREATE EXTENSION IF NOT EXISTS postgis_topology;
        CREATE EXTENSION IF NOT EXISTS pldbgapi;
        CREATE EXTENSION IF NOT EXISTS pg_cron;
EOSQL
done