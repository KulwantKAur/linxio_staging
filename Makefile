# export to makefile only env vars that are in file
# export $(shell sed 's/=.*//' ../../../.env)

CONTAINER := php
CONSOLE=php bin/console
BEHAT=vendor/bin/behat
ELK=fos:elastica:populate
MEMORY=memory_limit=1024M

build:
	@docker-compose build
	@docker-compose up -d

stop_containers:
	@docker-compose stop

composer:
	@docker-compose exec -T $(CONTAINER) composer install --ignore-platform-reqs --prefer-dist
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) cache:clear

post_build_test:
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) --no-interaction doctrine:migrations:migrate --env=test
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) --no-interaction doctrine:fixtures:load --append --env=test
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) fos:elastica:populate --env=test

post_build_dev:
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) cache:clear --no-warmup
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) --no-interaction doctrine:migrations:migrate
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) --no-interaction doctrine:fixtures:load --append
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) db:procedures:insert
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) cache:pool:clear cache.global_clearer

post_build_prod:
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) cache:clear --no-warmup
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) --no-interaction doctrine:migrations:migrate
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) --no-interaction doctrine:fixtures:load --append --group=global
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) db:procedures:insert
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) cache:pool:clear cache.global_clearer
	@docker-compose exec -T $(CONTAINER) $(CONSOLE) fos:elastica:populate

post_elk:
	@docker-compose exec -T $(CONTAINER) bash -c "php -d memory_limit=1024M bin/console fos:elastica:populate"

test:
	-@docker-compose exec -T $(CONTAINER) bash -c "php -d memory_limit=1024M $(BEHAT)"
	@make -s down

run_queue:
	@docker-compose exec -T $(CONTAINER) nohup $(CONSOLE) rabbitmq:consumer events &
	@docker-compose exec -T $(CONTAINER) nohup $(CONSOLE) rabbitmq:consumer sms &
	@docker-compose exec -T $(CONTAINER) nohup $(CONSOLE) rabbitmq:consumer email &
	@docker-compose exec -T $(CONTAINER) nohup $(CONSOLE) rabbitmq:consumer webapp &

down:
	@docker-compose down
	@make -s clean

clean:
	@docker system prune --volumes --force
