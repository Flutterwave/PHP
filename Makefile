.PHONY: init
check:
	@echo "Installing dependencies..."
	@composer install
	@echo "Installing dependencies... Done"
	@./vendor/bin/pest --coverage --min=0 --coverage-clover ./coverage.xml


test:
	@./vendor/bin/pest --coverage --min=0 --coverage-clover ./coverage.xml

debug:
	XDEBUG_MODE=coverage ./vendor/bin/pest --coverage --coverage-html .log


