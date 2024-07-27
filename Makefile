DOCKER_COMPOSE := docker compose
DOCKER_EXEC := $(DOCKER_COMPOSE) exec

# Replace 'your-container-name' with the actual name of your Docker container
CONTAINER_NAME := booking-system

# Makefile target to enter the Docker container and run 'composer install'
php-artisan:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'php artisan $(command)'

migrate:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'php artisan migrate --seed'

composer-i:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'composer install --no-interaction'

composer-u:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'composer update --no-interaction --with-all-dependencies {-W}'

composer-add:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'composer require i$(package)'

npm-i:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'npm install $(command) --no-interaction'

npm-add:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'npm install $(package) --no-interaction'

fix-laravel-perms:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'chmod -R 777 storage database'

build:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh -c 'npm run build'


install: composer-i npm-i build fix-laravel-perms

sh:
	$(DOCKER_EXEC) $(CONTAINER_NAME) sh

# Add more targets as needed

.PHONY: composer-install composer-update composer-require

