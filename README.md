Linxio API
=========================
### Install Guide

- install local Docker and Docker Compose
- clone current repository
- navigate to project folder
- run ````./bin/install.sh```` to init project setup [@TODO move steps below to this script]
- run ````cp app/config/parameters.yml.dist app/config/parameters.yml```` to rename PARAMETERS files
- run console command ````echo 'vm.max_map_count=262144' >> /etc/sysctl.conf```` for settings elasticsearch memory configuration
- run following command (!!!important - use `linxio` as pass phrase of certificate, this phrase is hardcoded in /app/config/config.yml):
    - ````mkdir -p app/config/jwt````
    - ````openssl genpkey -out app/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096````
    - ````openssl pkey -in app/config/jwt/private.pem -out app/config/jwt/public.pem -pubout````
- make sure containers `provider` and `provider2` from project `Linxio Trackers` are in the same network and available, check `networks` in `docker-compose.yml`
- run ````docker-compose -p linxio build```` to build containers
- run ````docker-compose -p linxio up -d```` to start containers

After that backend will be available at http://127.0.0.1:8098

Portainer (docker containers management) will be available at `http://127.0.0.1:9010`.
Default user: `admin`, default password: `12345678`

### Replica params:
(For long queries)

max_standby_archive_delay = 900s

max_standby_streaming_delay = 900s

### Migration to new DoctrineMigrationsBundle
- run ````php bin/console doctrine:migrations:sync-metadata-storage````

### Traccar
Requirements: `psql`

Installation steps are automatically after execution file `./bin/install.sh`

WEB UI is available at: http://localhost:8082

Credentials by default - admin: admin

Cron job is used with migration for traccar DB changes: `src/Service/Traccar/TraccarMigrationService.php`

### Scripts
- create startup.sh from example startup.sh.example - script for starting environment after server reboot.
Then add this line (example) "/var/www/linxio_api/startup.sh" to the file /etc/rc.local before line "exit 0"

### Deploy

####Notification consumers

Check running command:
````
ps aux | grep rabbitmq:consumer
````

Note: Kill if exist.

Run scripts.

````
nohup docker-compose run --rm --no-deps php php bin/console rabbitmq:consumer events &
````

````
nohup docker-compose run --rm --no-deps php php bin/console rabbitmq:consumer email &
````

````
nohup docker-compose run --rm --no-deps php php bin/console rabbitmq:consumer areas &
````

#### Queues with RabbitMQ multiple consumers

````
php bin/console rabbitmq:multiple-consumer -v -l 256 --no-debug tracker_engine_on_time_multiple
````

#### Cron

Cron for call command `app:notifications:send`

````
*/1 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_notifications php php /srv/bin/console app:notifications:send >> /var/log/cron.log 2>&1
*/60 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps php php /srv/bin/console app:reminder:update-statuses >> /var/log/cron.log 2>&1
*/60 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps php php /srv/bin/console app:document:update-statuses >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_calculate_routes php php /srv/bin/console app:tracker:calculate-routes >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_calculate_idlings php php /srv/bin/console app:tracker:calculate-idling >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_calculate_speedings php php /srv/bin/console app:tracker:calculate-speeding >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_route_location php php /srv/bin/console app:tracker:update-route-location >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_update_wrong_routes php php /srv/bin/console app:tracker:update-wrong-routes >> /var/log/cron.log 2>&1
0 19 * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_prism_export php php /srv/bin/console app:export-vehicle-data >> /var/log/cron.log 2>&1
0 18 * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_clear_temp_history php php /srv/bin/console app:tracker:clear-temp-history >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_sensor_status php php /srv/bin/console app:tracker:update-device-sensor-status >> /var/log/cron.log 2>&1
*/60 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_scheduled_report php php /srv/bin/console app:scheduled-report >> /var/log/cron.log 2>&1
*/30 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_asset_missed php php /srv/bin/console app:tracker:asset-missed >> /var/log/cron.log 2>&1
0 0 * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_fleetio php php /srv/bin/console app:send-fleetio-data >> /var/log/cron.log 2>&1
0 0 * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_vwork php php /srv/bin/console app:send-vwork-data >> /var/log/cron.log 2>&1
*/60 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_vehicle_update_data php php /srv/bin/console app:vehicle:update-data >> /var/log/cron.log 2>&1
* * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_user_update_network_status php php /srv/bin/console app:user:update-network-status >> /var/log/cron.log 2>&1
50 * * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_device_update_data php php /srv/bin/console app:device:update-data >> /var/log/cron.log 2>&1
0 18 * * * cd /var/www/linxio/api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_chat_update_data php php /srv/bin/console app:chat:update-data >> /var/log/cron.log 2>&1
*/5 * * * * cd /var/www/linxio_api && /usr/local/bin/docker-compose -f docker-compose.yml run --rm --no-deps --name php_vehicle_driver_logout php php /srv/bin/console app:vehicle:driver-logout >> /var/log/cron.log 2>&1
````

#### PostgreSQL Cron (pg_cron)

How to use: https://github.com/citusdata/pg_cron

List of jobs (WIP):

```
SELECT cron.schedule('create_new_tracker_history_temp_partitions', '00 12 * * *', $$SELECT create_partitions('tracker_history_temp_part', 'thtp', '1 day', 'day', 'YYYY_MM_DD', 3, 0, ARRAY['tracker_history_id']);$$);
SELECT cron.schedule('delete_old_tracker_history_temp_partitions', '05 12 * * *', $$SELECT delete_partitions('tracker_history_temp_part', 'thtp', '1 day', 'YYYY_MM_DD', 30, 25);$$);

```

#### PL/pgSQL Procedures


````
Note: For add new procedure. Create new class in namespace `App\Resources\procedures` and implement `InsertProcedureInterface`.
````

````
Alert: If you change db procedure signature. Add new migration for remove old function.
````

##### Driving behavior

1. Get excessive speed periods:
````
get_excessive_speed_periods (
    excessive_speed_map JSON,
    select_date_start TIMESTAMP,
    select_date_end TIMESTAMP,
    vehicle_ids INT []
)
````

Return Table
````
(
    _ids INT [], vehicle INT,
    start_period TIMESTAMP,
    end_period TIMESTAMP,
    avg_speed DECIMAL,
    distance DECIMAL
)
````

Using
````
SELECT * FROM get_excessive_speed_periods('{"27": 85, "11": 80}'::JSON, '2019-10-30 11:28:15', '2019-10-30 11:40:15',  ARRAY[27,11])
````

2. Get idling periods:

````
get_idling_periods(select_date_start TIMESTAMP, select_date_end TIMESTAMP, vehicle_ids INT[])
````

Return Table:
````
(_ids INT[], vehicle INT, start_period TIMESTAMP, end_period TIMESTAMP)
````

Using:
````
SELECT * FROM idlings('2019-11-04 12:55:55', '2019-11-04 12:58:14', ARRAY[27, 11])
````

##### Update INT to BIGINT

Usage:
- init cron-job and handling for specific column via `SELECT int_to_bigint_create_data();`
- wait for all updates by job (check `job_run_details`)
- make final handle via `SELECT int_to_bigint_final_clear_data();`. You will receive SQL commands in console output to add them manually to create new foreign keys

Example for foreign key (related to primary):
````
SELECT int_to_bigint_create_data('speeding', 'point_finish_id', false, false, false, 'tracker_history', 'id', false, 100000);
````
````
SELECT int_to_bigint_final_clear_data('speeding', 'point_finish_id', false);
````

Example for primary key:
````
SELECT int_to_bigint_create_data('tracker_payload', 'id', true, true, false, null, null, false, 100000);
````
````
SELECT int_to_bigint_final_clear_data('tracker_payload', 'id', true, false);
````

#### Centrifugo

Installation steps are automatically after execution file `./bin/install.sh`

Config file: `devops/centrifugo/config.json`

Param `token_rsa_public_key` should be equal to `app/config/jwt/public.pem`

WEB UI is available at: http://localhost:8000
Password by default: `password`

#### Tests

Command for running tests:
Before running tests - run 'rabbit-test' container

````
./vendor/bin/behat
````
Params:
````
--suite=.... - name of suite from behat.yml (ex: users, clients and etc)
--name="...." - name of feature from suite (ex: "I want to register and login without 2FA")
````

Ps: If some problems with database in tests - recreate test database:
````
php bin/console doctrine:database:drop --env=test --force
php bin/console doctrine:database:create --env=test
RUN SQL: 
    CREATE EXTENSION IF NOT EXISTS postgis;
    CREATE EXTENSION IF NOT EXISTS pg_cron;
Close all connections to test database from ide.
````