.PHONY: ci test cs phpunit phpcs stan psalm

ci: test cs
test: phpunit
cs: phpcs stan psalm

phpunit:
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist

perf:
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist --group Performance

phpcs:
	cd ../.. && vendor/bin/phpcs -p -s --standard=$(shell pwd)/phpcs.xml

stan:
	../../vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G

stan-baseline:
	../../vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G --generate-baseline

psalm:
	../../vendor/bin/psalm --config=psalm.xml

psalm-baseline:
	../../vendor/bin/psalm --config=psalm.xml --set-baseline=psalm-baseline.xml
