tests:
	./vendor/bin/phpunit
.PHONY: tests

setup:
	composer install
.PHONY: setup
