# Developer entry points. `make verify` runs the PHP and workflow quality gates.
.DEFAULT_GOAL := help

COMPOSER ?= composer
PHP ?= php
PYTHON ?= python3
NPM ?= npm
ACTIONLINT ?= actionlint
COVERAGE_PHP_FLAGS ?= -d pcov.enabled=1
INFECTION_THREADS ?= max

# PHPUnit and Infection share caches and coverage output, including with -j.
.NOTPARALLEL:
.PHONY: help install validate fmt fmt-check lint analyze workflows test test-release coverage infection audit docs docs-dev docs-preview verify clean

help: ## List developer commands
	@awk 'BEGIN { FS = ":.*## " } /^[a-z-]+:.*## / { printf "  %-15s %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

install: ## Install PHP and documentation dependencies
	$(COMPOSER) install --no-interaction --prefer-dist
	$(NPM) ci --prefix docs

validate: ## Validate Composer metadata and lockfile
	$(COMPOSER) validate --strict

fmt: ## Apply Mago formatting
	$(COMPOSER) format

fmt-check: ## Check Mago formatting without changes
	$(COMPOSER) format:check

lint: ## Run Mago lint rules
	$(COMPOSER) lint

analyze: ## Run Mago static analysis
	$(COMPOSER) analyze

workflows: ## Validate GitHub workflows and shell scripts
	$(ACTIONLINT)

test: test-release ## Run PHPUnit and release-helper tests
	$(COMPOSER) test

test-release: ## Run release-helper tests
	$(PYTHON) -m unittest discover -s tests -p '*_test.py'

coverage: ## Collect coverage and require 100% of statements
	XDEBUG_MODE=coverage $(PHP) $(COVERAGE_PHP_FLAGS) vendor/bin/phpunit --coverage-clover=var/coverage/clover.xml --coverage-xml=var/coverage/xml --log-junit=var/coverage/junit.xml
	$(PYTHON) tools/check-coverage.py var/coverage/clover.xml

infection: ## Require 100% overall MSI and covered MSI
	XDEBUG_MODE=coverage $(PHP) $(COVERAGE_PHP_FLAGS) vendor/bin/infection --threads=$(INFECTION_THREADS) --initial-tests-php-options="$(COVERAGE_PHP_FLAGS)"

audit: ## Check Composer dependencies for security advisories
	$(COMPOSER) audit

docs: ## Install dependencies and build the VitePress site
	$(NPM) ci --prefix docs
	$(NPM) run docs:build --prefix docs

docs-dev: ## Install dependencies and serve docs with live reload
	$(NPM) ci --prefix docs
	$(NPM) run docs:dev --prefix docs

docs-preview: ## Build and preview the documentation site
	$(MAKE) docs
	$(NPM) run docs:preview --prefix docs

verify: validate fmt-check lint analyze workflows test coverage infection audit ## Run all PHP and workflow quality gates

clean: ## Remove generated coverage, mutation and documentation artifacts
	rm -rf .phpunit.cache var/coverage docs/.vitepress/cache docs/.vitepress/dist
	rm -f var/infection.log var/infection-summary.log
