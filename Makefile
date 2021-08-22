lint:
	vendor/bin/phpcs --standard=phpcs.xml
.PHONY: lint

lint-fix:
	vendor/bin/phpcbf --standard=phpcs.xml
.PHONY: lint-fix

test:
	vendor/bin/phpunit
.PHONY: test

coverage:
	vendor/bin/phpunit --coverage-text --coverage-html .coverage
.PHONY: coverage

mutation:
	vendor/bin/infection --threads=4
.PHONY: mutation

phpstan:
	vendor/bin/phpstan
.PHONY: phpstan

psalm:
	vendor/bin/psalm
.PHONY: psalm
