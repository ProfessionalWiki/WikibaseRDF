.PHONY: ci test cs phpunit phpcs stan psalm

ci: test cs
test: phpunit
cs: phpcs stan psalm

phpunit:
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist

phpcs:
	cd ../.. && vendor/bin/phpcs -p -s --standard=$(shell pwd)/phpcs.xml

stan:
	../../vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G

psalm:
	../../vendor/bin/psalm --config=psalm.xml

