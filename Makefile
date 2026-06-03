ROOT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
OS := $(shell uname)

default: reload
#default: start

deploy:
	php ./deployer.phar deploy develop

ash:
	docker exec -it pm-php81 ash

web:
	docker exec -it pm-php81 ash

db:
	docker exec -it pm-php81 bash

dump:
	@ echo "Have you meaning 'make db-dump' or 'make autoload'?"

push-all:
	docker-compose push

start:
	@(echo "-> Starting application docker (local)")
	make restart
	@(./do.sh "php composer.phar install")
	@(echo "-> Done")

docker-build:
	@(echo "-> Building application docker (local)")
	docker-compose build
	@(echo "-> Done")

stop:
	@(echo "-> Stopping application docker (sync on mac)...")
	docker-compose stop
	@(echo "-> Done")

down:
	@(echo "-> Stopping & removing the application...")
	docker-compose down --remove-orphans
	@(echo "-> Done")

restart:
	docker-compose pull
	@ make reload

pull:
	docker-compose pull
	@ make reload

reload:
	docker-compose up -d --force-recreate
	@(./do.sh "mkdir bootstrap/cache")
	@(./do.sh "php composer.phar install")

fresh: refresh
refresh:
	@(echo "-> Refresh the application")
	@(./do.sh "php artisan migrate:fresh")
	@(./do.sh "php artisan db:seed")
	@(echo "-> Done")

migrate:
	@(echo "-> Running migrations...")
	@(./do.sh "php artisan migrate")
	@(echo "-> Done")

rollback:
	@(echo "-> Running migrations rollback...")
	@(./do.sh "php artisan migrate:rollback")
	@(echo "-> Done")

install:
	composer --ignore-platform-reqs --no-scripts install

update:
	composer --ignore-platform-reqs --no-scripts update
	@(./do.sh "composer install")

update-nodev:
	composer --ignore-platform-reqs --no-scripts --no-dev update
	@(./do.sh "composer install")

composer_install:
	@(echo "-> Installing composer dependencies...")
	@(./do.sh "php composer.phar install")
	@(echo "-> Done")

composer_update:
	@(echo "-> Updating composer dependencies...")
	@(./do.sh "php composer.phar update")
	@(echo "-> Done")

composer_dump:
	@(echo "-> Dump composer autoload...")
	@(./do.sh "composer dump-autoload")
	@(echo "-> Done")

autoload:
	@ printf " \r-> Dumping composer autoload @ host... [`date`]\n"
	php -d memory_limit=-1 composer.phar dump-autoload -v --no-scripts
	@ printf " -> Done @ `date`\n"

require:
	@ printf " \r-> Require composer package @ host... [`date`]\n"
	composer --ignore-platform-reqs --no-scripts -v require $(filter-out $@,$(MAKECMDGOALS))
	@ # php -d memory_limit=-1 composer.phar --ignore-platform-reqs --no-scripts -v require $(filter-out $@,$(MAKECMDGOALS))
	@ printf " -> Done @ `date`\n"

require-dev:
	@ printf " \r-> Require composer package (DEV) @ host... [`date`]\n"
	php -d memory_limit=-1 composer.phar --ignore-platform-reqs --no-scripts --dev -v require $(filter-out $@,$(MAKECMDGOALS))
	@ printf " -> Done @ `date`\n"

install_composer:
	@ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	@ php composer-setup.php
	@ php -r "unlink('composer-setup.php');"

arg:
	@ echo "<<<"
	@ echo $(filter-out $@,$(MAKECMDGOALS))
	@ echo ">>>"

lint:
	@(echo "-> Running php lint...")
	@(./vendor/bin/phpcs --standard=ruleset.xml app -p)
	@(echo "-> Done")

test:
	@(echo "-> Running tests (dockered)...")
	@(./do.sh ./vendor/bin/phpunit)
	@(echo "-> Done")

clean:
	@(echo "-> Refresh caches (dockered)...")
	@(./do.sh "rm -rf bootstrap/cache/*.php")
	@(./do.sh "php artisan optimize")
	@(./do.sh "php artisan view:clear")
	@(./do.sh "php artisan cache:clear")
	@(./do.sh "php artisan debugbar:clear")
	@(echo "-> Done")

scribe:
	@ make clean
	@(./do.sh "rm -rf resources/docs/groups/endpoints.md")
	@(./do.sh "php artisan scribe:generate --force -n -vvv")

ide:
	@(echo "-> IDE helper: make `_ide_helper.php`, write models properties...")
	php artisan ide-helper:models -W -R
	@(echo "-> Done")

ide_full:
	@(echo "-> IDE helper: generating all smelly things...")
	php artisan ide-helper:generate
	php artisan ide-helper:meta
	@(./do.sh "php artisan ide-helper:models -W -R")
	@(echo "-> Done")

npm:
	npm update
	npm audit fix
	npm run prod