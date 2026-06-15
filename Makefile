SHELL := /usr/bin/env bash
.DEFAULT_GOAL := help

COMPOSE := docker compose -f compose.yaml
PROD_COMPOSE := docker compose --env-file .env.production -f compose.production.yaml

.PHONY: help doctor init build up down restart ps logs shell artisan composer npm dev build-assets test quality migrate seed cache-clear backup restore prod-build prod-up prod-down prod-logs docs-check

help:
	@printf "%s\n" \
	"post_md-web" \
	"" \
	"  make doctor                  Verifica pré-requisitos" \
	"  make init                    Instala Laravel e inicializa o ambiente" \
	"  make build                   Constrói imagens de desenvolvimento" \
	"  make up                      Sobe o ambiente" \
	"  make down                    Para containers sem apagar volumes" \
	"  make restart                 Reinicia o ambiente" \
	"  make ps                      Exibe serviços" \
	"  make logs                    Acompanha logs" \
	"  make shell                   Abre shell no app" \
	"  make artisan CMD=\"about\"    Executa Artisan" \
	"  make composer CMD=\"show\"    Executa Composer" \
	"  make npm CMD=\"run build\"    Executa npm" \
	"  make migrate                 Executa migrations" \
	"  make seed                    Executa seeders" \
	"  make test                    Executa testes" \
	"  make quality                 Testes, Pint e build" \
	"  make backup                  Backup PostgreSQL" \
	"  make restore FILE=... CONFIRM=restore" \
	"  make prod-build              Constrói imagens de produção" \
	"  make prod-up                 Sobe produção" \
	"  make docs-check              Verifica documentação"

doctor:
	@./scripts/doctor.sh

init:
	@./scripts/init.sh

build:
	@$(COMPOSE) build

up:
	@$(COMPOSE) up -d

down:
	@$(COMPOSE) down

restart:
	@$(COMPOSE) restart

ps:
	@$(COMPOSE) ps

logs:
	@$(COMPOSE) logs -f --tail=200

shell:
	@$(COMPOSE) exec app bash

artisan:
	@$(COMPOSE) exec app php artisan $(CMD)

composer:
	@$(COMPOSE) exec app composer $(CMD)

npm:
	@$(COMPOSE) exec node npm $(CMD)

dev:
	@$(COMPOSE) up -d node
	@$(COMPOSE) logs -f node

build-assets:
	@$(COMPOSE) run --rm node npm run build

migrate:
	@$(COMPOSE) exec app php artisan migrate

seed:
	@$(COMPOSE) exec app php artisan db:seed

cache-clear:
	@$(COMPOSE) exec app php artisan optimize:clear

test:
	@$(COMPOSE) exec app php artisan test

quality:
	@$(COMPOSE) exec app ./vendor/bin/pint --test
	@$(COMPOSE) exec app php artisan test
	@$(COMPOSE) run --rm node npm run build
	@./scripts/docs-check.sh

backup:
	@./scripts/backup.sh

restore:
	@./scripts/restore.sh "$(FILE)" "$(CONFIRM)"

prod-build:
	@$(PROD_COMPOSE) build --pull

prod-up:
	@$(PROD_COMPOSE) up -d

prod-down:
	@$(PROD_COMPOSE) down

prod-logs:
	@$(PROD_COMPOSE) logs -f --tail=200

docs-check:
	@./scripts/docs-check.sh
