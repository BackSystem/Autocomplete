.PHONY: stan
stan:
	./vendor/phpstan/phpstan/phpstan

.PHONY: format
format:
	PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --using-cache=no --diff