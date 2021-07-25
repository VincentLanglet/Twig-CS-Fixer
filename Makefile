lint:
	vendor/bin/phpcs --standard=phpcs.xml
.PHONY: lint

test:
	vendor/bin/phpunit
.PHONY: test

phpstan:
	vendor/bin/phpstan
.PHONY: phpstan
