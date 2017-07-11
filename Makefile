.PHONY: help install test report report-tap spec-test unit-test unit-report unit-report-tap functional-test functional-report functional-report-tap

help:
	@echo "Available commands:"
	@echo ""
	@echo "  help                  list of available make commands"
	@echo "  install               installs all dependencies (composer, npm, bower)"
	@echo "  test                  launch all tests (spec, unit, functional)"
	@echo "  report                generate code coverage reports for unit and functional tests"
	@echo "  report-tap            generate code coverage reports for unit and functional tests with --tap parameter"
	@echo "  spec-test             launch PhpSpec tests"
	@echo "  unit-test             launch PHPUnit unit tests"
	@echo "  unit-report           launch PHPUnit unit tests with code coverage reports"
	@echo "  unit-report-tap       launch PHPUnit unit tests with code coverage reports and --tap parameter"
	@echo "  functional-test       launch PHPUnit functional tests"
	@echo "  functional-report     launch PHPUnit functional tests with code coverage reports"
	@echo "  functional-report-tap launch PHPUnit functional tests with code coverage reports and --tap parameter"
	@echo ""

install:
	composer install

test: spec-test unit-test functional-test

report: unit-report functional-report

report-tap: unit-report-tap functional-report-tap

spec-test:
	@echo "NO PHPSPEC TESTS"

unit-test:
	@echo "PHPUNIT -- UNIT TESTS"
	vendor/bin/phpunit -c build/phpunit_unit_reports.xml --no-coverage

unit-report:
	@echo "PHPUNIT -- UNIT TESTS -- WITH REPORT"
	vendor/bin/phpunit -c build/phpunit_unit_reports.xml

unit-report-tap:
	@echo "PHPUNIT -- UNIT TESTS -- WITH REPORT"
	vendor/bin/phpunit -c build/phpunit_unit_reports.xml --tap

functional-test:
	@echo "PHPUNIT -- FUNCTIONAL TESTS"
	vendor/bin/phpunit -c build/phpunit_functional_reports.xml --no-coverage

functional-report:
	@echo "PHPUNIT -- FUNCTIONAL TESTS -- WITH REPORT AND TAP"
	vendor/bin/phpunit -c build/phpunit_functional_reports.xml

functional-report-tap:
	@echo "PHPUNIT -- FUNCTIONAL TESTS -- WITH REPORT AND TAP"
	vendor/bin/phpunit -c build/phpunit_functional_reports.xml --tap
