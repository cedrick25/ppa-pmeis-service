CONTAINER := pmeis-service-app
MYSQL_CONTAINER := pmeis-mysql

build:
	docker-compose build

start:
	docker-compose up -d

watch:
	docker-compose up

stop:
	docker-compose down --remove-orphans

ssh:
	docker exec -it ${CONTAINER} /bin/bash

ssh-mysql:
	docker exec -it ${MYSQL_CONTAINER} /bin/bash

composer-install:
	docker exec ${CONTAINER} bash -c "composer require $(package)"

clear-cache:
	docker exec ${CONTAINER} bash -c "php bin/console cache:clear"

create-controller:
	docker exec ${CONTAINER} bash -c "php bin/console make:controller $(name)"

migrate:
	docker exec ${CONTAINER} bash -c "php bin/console doctrine:migration:migrate"

create-entity:
	docker exec ${CONTAINER} bash -c "php bin/console make:entity"

create-migration:
	docker exec ${CONTAINER} bash -c "php bin/console doctrine:migration:generate"

create-migration-diff:
	docker exec ${CONTAINER} bash -c "php bin/console doctrine:migrations:diff"