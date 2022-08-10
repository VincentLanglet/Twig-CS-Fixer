lint:
	vendor/bin/php-cs-fixer fix --dry-run
.PHONY: lint

lint-fix:
	vendor/bin/php-cs-fixer fix
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
