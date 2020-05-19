.PHONY: dependencies, unit-tests, integration-tests, test-coverage, style-check, dependency-check, static-analysis, style-fix, docker-tests, cli
build:
	@make dependencies && make dependency-check && make static-analysis && make style-check && make unit-tests && make integration-tests

dependencies:
	@composer install

unit-tests:
	@vendor/bin/phpunit --bootstrap=./tests/bootstrap.php --testsuite Unit

integration-tests:
	@vendor/bin/phpunit --bootstrap=./tests/bootstrap.php --testsuite Integration

test-coverage:
	@vendor/bin/phpunit --coverage-html ./coverage

style-check:
	@vendor/bin/phpcs --standard=PSR12 ./src/* ./tests/*

dependency-check:
	@vendor/bin/composer-require-checker --config-file=composer-require-checker-config.json check -vvv ./composer.json

static-analysis:
	@vendor/bin/phpstan analyze && ./vendor/bin/psalm --show-info=false

style-fix:
	@vendor/bin/phpcbf --standard=PSR12 ./src ./tests

docker-tests:
	@docker-compose -f docker-compose.yml -f docker-compose-tests.yml up --abort-on-container-exit --exit-code-from php

cli:
	@docker-compose up -d && ./vendor/bin/psysh ./bootstrap/cli.php