SHELL := /usr/bin/env bash
DC := -f docker-compose.yml -f docker-compose.dev.yml
PHP := docker-compose ${DC} exec php
PHPQA := docker run --init -it --rm -v ${PWD}:/project -v ${PWD}/tmp-phpqa:/tmp -w /project jakzal/phpqa:alpine
CONTENT_TYPE ?= application/json
ACCEPT ?= application/json
SAMPLES ?= 1
ENV = dev


setup-rabbit:
	docker exec -it rabbit_rabbitmq_1 rabbitmqctl add_user andrzej 123123
	docker exec -it rabbit_rabbitmq_1 rabbitmqctl add_vhost test_dev
	docker exec -it rabbit_rabbitmq_1 rabbitmqctl set_permissions -p test_dev andrzej ".*" ".*" ".*"

docker-restart: docker-down docker-up
docker-up:
	docker-compose ${DC} up -d --force-recreate
docker-up2:
	docker-compose ${DC} up --force-recreate
docker-build:
	docker-compose ${DC} build --no-cache
docker-down:
	docker-compose ${DC} down --remove-orphans
docker-logs:
	docker-compose ${DC} logs -f
docker-ps:
	docker-compose ${DC} ps
docker-cmd:
	docker-compose ${DC} ${CMD}
docker-bash:
	${PHP} /bin/bash
docker-kill:
	docker kill $(shell docker ps -q)


composer-require:
	${PHP} composer require ${PACKAGE}
composer-require-dev:
	${PHP} composer require --dev ${PACKAGE}
composer-remove:
	${PHP} composer remove ${PACKAGE}
composer-install:
	${PHP} composer install
composer-update:
	${PHP} composer update
composer-show:
	${PHP} composer show


symfony:
	${PHP} bin/console ${CMD}
php:
	${PHP} ${FILE}
xdebug-enable:
	${PHP} /xdebug-enable.sh
xdebug-disable:
	${PHP} /xdebug-disable.sh
bash:
	${PHP} bash
cache-clear:
	docker-compose ${DC} exec redis redis-cli FLUSHALL
	sudo chmod -R a+rw var
	rm -rfv var/cache/*
	rm -rfv var/log/*
	rm -rfv var/tmp/*