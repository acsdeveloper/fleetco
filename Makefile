.PHONY: help build up down restart logs shell db-shell install dev-install lint

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

# ─── Docker ───────────────────────────────────────────────────────────────────

build: ## Build production image
	docker compose build app

build-dev: ## Build development image
	docker compose build app-dev

up: ## Start production stack (detached)
	docker compose up -d app db

dev: ## Start development stack (detached)
	docker compose --profile dev up -d app-dev db

down: ## Stop and remove containers
	docker compose down

restart: ## Restart the app container
	docker compose restart app

logs: ## Tail app logs
	docker compose logs -f app

shell: ## Open shell in running app container
	docker compose exec app bash

db-shell: ## Open MySQL shell
	docker compose exec db mysql -u$${DB_USER:-fleetco} -p$${DB_PASSWORD:-fleetco} $${DB_NAME:-fleetco}

db-dump: ## Dump the database to ./backup.sql
	docker compose exec db mysqldump -u root -p$${DB_ROOT_PASSWORD:-rootpassword} $${DB_NAME:-fleetco} > backup.sql

db-restore: ## Restore database from ./backup.sql
	docker compose exec -T db mysql -u root -p$${DB_ROOT_PASSWORD:-rootpassword} $${DB_NAME:-fleetco} < backup.sql

# ─── Local (no Docker) ────────────────────────────────────────────────────────

install: ## Install production PHP dependencies
	composer install --no-dev --optimize-autoloader

dev-install: ## Install all PHP dependencies (including dev)
	composer install

update: ## Update dependencies
	composer update

lint: ## Check PHP syntax on all app files
	find fleetco -name '*.php' -print0 | xargs -0 php -l | grep -v "No syntax errors" || true
