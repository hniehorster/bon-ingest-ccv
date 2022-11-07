build:
	composer dump
	make helpers

cron:
	while true; do \
		date; \
		php artisan schedule:run; \
		sleep $$((60 - $$(date +%S))); \
	done

cs:
	vendor/bin/php-cs-fixer fix -vv

consumers:
	php artisan bon_events:prepare consumers

publishers:
	php artisan bon_events:prepare publishers

consume_all:
	php artisan bon_events:consume all

debug:
	php artisan -vvv debug

test:
	 ./vendor/bin/phpunit

helpers:
	php artisan ide-helper:meta
	php artisan ide-helper:models --reset --write

logs:
	tail -f storage/logs/*.log

queue:
	php artisan queue:work -vvv --tries=1 --sleep=1 --queue=default

reset:
	php artisan migrate:fresh
	php artisan -vvv db:seed

update:
	composer install
	php artisan migrate
