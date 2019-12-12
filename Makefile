.DEFAULT_GOAL := check

.PHONY: check
check: lint phpstan

.PHONY: ci
ci: check

.PHONY: lint
lint:
	vendor/bin/parallel-lint Core

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse -l max -c phpstan.neon Core
