app = ofertilo
exec = docker exec -it $(execArgs)
exec-app = $(exec) $(app)

.DEFAULT_GOAL= help
.PHONY: help
help:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

.PHONY: cache-clean
cache-clean: cache-clean-file #cache-clean-app

#.PHONY: cache-clean-app
#cache-clean-app:
#	$(exec-app) php document_roots/admin/index.php cache:clean --all

.PHONY: cache-clean-file
cache-clean-file:
	$(exec-app) rm -rf var/cache

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
tester:
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

