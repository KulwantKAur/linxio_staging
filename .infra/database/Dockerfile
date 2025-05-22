FROM postgis/postgis:12-3.1

ENV PG_FULL 12-3.1

RUN mkdir -p /docker-entrypoint-initdb.d

# Install the postgresql debugger
#RUN apt-get update \
#  && apt-get install -y --no-install-recommends \
#  postgresql-$PG_FULL-pldebugger
RUN apt-get update \
    && apt-get -y install nano postgresql-12-cron

# Add custom config
ARG DB_NAME={$DB_NAME}
ARG conf=/usr/share/postgresql/postgresql.conf
RUN cat /usr/share/postgresql/postgresql.conf.sample > $conf
RUN echo "shared_preload_libraries = 'pg_cron'" >> $conf
RUN echo "cron.database_name = '$DB_NAME'" >> $conf
RUN cp $conf /etc/postgresql/postgresql.conf

COPY ./initdb-postgis.sh /docker-entrypoint-initdb.d/postgis.sh
COPY ./update-postgis.sh /usr/local/bin