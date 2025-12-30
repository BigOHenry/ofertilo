app = ofertilo
exec = docker exec -it $(execArgs)
exec-app = $(exec) $(app)

.DEFAULT_GOAL= help
.PHONY: help
help:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

.PHONY: cache-clean
cache-clean: cache-clean-file cache-clean-app

.PHONY: cache-clean-app
cache-clean-app:
	$(exec-app) php bin/console doctrine:cache:clear-metadata

.PHONY: cache-clean-file
cache-clean-file:
	$(exec-app) php bin/console cache:clear

.PHONY: db-create
db-create:
	$(exec-app) php bin/console make:migration

.PHONY: db-migrate
db-migrate:
	$(exec-app) php bin/console doctrine:migrations:migrate

.PHONY: phpcs
phpcs:
	$(exec-app) vendor/bin/phpcs

.PHONY: phpcs-fix
phpcs-fix:
	$(exec-app) vendor/bin/php-cs-fixer fix

.PHONY: phpstan
phpstan:
	$(exec-app) vendor/bin/phpstan analyse

.PHONY: phpunit
phpunit:
	$(exec-app) php bin/phpunit

.PHONY: composer
composer:
	$(exec-app) composer install

.PHONY: composer-update
composer-update:
	$(exec-app) composer update

.PHONY: npm
npm:
	$(exec-app) npm install

.PHONY: npm-update
npm-update:
	$(exec-app) npm update

.PHONY: npm-run-dev
npm-run-dev:
	$(exec-app) npm run dev

.PHONY: version
version:
	@git describe --tags --abbrev=0 > var/version.txt 2>/dev/null || echo "dev" > var/version.txt
	@echo "Version: $$(cat var/version.txt)"

.PHONY: deploy
deploy: version
	docker compose down
	docker compose build
	docker compose up -d
	sleep 5
	docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction
	docker compose exec -T app npm install
	docker compose exec -T app npm run build
	docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec -T app chown -R www-data:www-data /var/www/app/var
	docker compose exec -T app php bin/console cache:clear --no-interaction

