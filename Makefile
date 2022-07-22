CONTAINER := pmeis-service-app

build:
	docker-compose build

start:
	docker-compose up -d

stop:
	docker-compose down --remove-orphans

ssh:
	docker exec -it ${CONTAINER} /bin/bash

composer-install:
	docker exec ${CONTAINER} bash -c "composer require $(package)"

clear-cache:
	docker exec ${CONTAINER} bash -c "php bin/console cache:clear"

create-controller:
	docker exec ${CONTAINER} bash -c "php bin/console make:controller $(name)"

migrate:
	docker exec ${CONTAINER} bash -c "php bin/console doctrine:migration:migrate"