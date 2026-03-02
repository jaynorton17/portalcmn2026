SHELL := /bin/bash

MANIFEST_FILE := docs/endpoint-manifest.json

.PHONY: manifest validate validate-handlers validate-runtime check

manifest:
	@echo "==> Generating endpoint manifest"
	php scripts/generate_endpoint_manifest.php $(MANIFEST_FILE)

validate:
	@echo "==> Validating endpoint policies"
	php scripts/validate_endpoint_policies.php $(MANIFEST_FILE)

validate-handlers:
	@echo "==> Validating handler registrations"
	php scripts/validate_handler_registrations.php $(MANIFEST_FILE)

validate-runtime:
	@echo "==> Validating runtime guard contracts"
	php scripts/validate_runtime_guards.php $(MANIFEST_FILE)

check:
	@echo "==> PHP lint"
	php -l covermenowone-one.php
	@echo "==> Node syntax check"
	node --check frontend.js
	@$(MAKE) manifest
	@$(MAKE) validate
	@$(MAKE) validate-handlers
	@$(MAKE) validate-runtime
