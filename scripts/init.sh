#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
  cp .env.example .env
  printf 'Criado .env a partir de .env.example.\n'
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

APP_UID="${APP_UID:-$(id -u)}"
APP_GID="${APP_GID:-$(id -g)}"
APP_PORT="${APP_PORT:-8000}"
VITE_PORT="${VITE_PORT:-5173}"
LARAVEL_VERSION="${LARAVEL_VERSION:-^13.0}"
POSTGRES_DB="${POSTGRES_DB:-post_md}"
POSTGRES_USER="${POSTGRES_USER:-post_md}"
POSTGRES_PASSWORD="${POSTGRES_PASSWORD:-change_me_postgres}"
REDIS_PASSWORD="${REDIS_PASSWORD:-change_me_redis}"

mkdir -p src backups

if [[ -f src/artisan ]]; then
  printf 'Laravel já existe em src/. O bootstrap não será repetido.\n'
else
  unexpected="$(find src -mindepth 1 -maxdepth 1 ! -name '.gitkeep' -print -quit)"

  if [[ -n "$unexpected" ]]; then
    printf 'ERRO: src/ não está vazia e não contém uma aplicação Laravel válida.\n' >&2
    printf 'Revise os arquivos antes de continuar: %s\n' "$unexpected" >&2
    exit 1
  fi

  rm -f src/.gitkeep

  docker compose -f compose.yaml build app

  docker compose -f compose.yaml run --rm --no-deps \
    app composer create-project \
    --prefer-dist \
    --no-interaction \
    "laravel/laravel:${LARAVEL_VERSION}" .

  cp docker/laravel/.env.example src/.env
  cp docker/laravel/.env.example src/.env.example

  escape_sed() {
    printf '%s' "$1" | sed 's/[\/&]/\\&/g'
  }

  sed -i \
    -e "s/__APP_PORT__/$(escape_sed "$APP_PORT")/g" \
    -e "s/__VITE_PORT__/$(escape_sed "$VITE_PORT")/g" \
    -e "s/__POSTGRES_DB__/$(escape_sed "$POSTGRES_DB")/g" \
    -e "s/__POSTGRES_USER__/$(escape_sed "$POSTGRES_USER")/g" \
    -e "s/__POSTGRES_PASSWORD__/$(escape_sed "$POSTGRES_PASSWORD")/g" \
    -e "s/__REDIS_PASSWORD__/$(escape_sed "$REDIS_PASSWORD")/g" \
    src/.env

  sed -i \
    -e "s/__APP_PORT__/8000/g" \
    -e "s/__VITE_PORT__/5173/g" \
    -e "s/__POSTGRES_DB__/post_md/g" \
    -e "s/__POSTGRES_USER__/post_md/g" \
    -e "s/__POSTGRES_PASSWORD__/change_me/g" \
    -e "s/__REDIS_PASSWORD__/change_me/g" \
    src/.env.example
fi

docker compose -f compose.yaml up -d db redis

printf 'Aguardando PostgreSQL e Redis...\n'
for _ in $(seq 1 60); do
  db_health="$(docker inspect --format='{{.State.Health.Status}}' "${COMPOSE_PROJECT_NAME:-post_md_web}-db-1" 2>/dev/null || true)"
  redis_health="$(docker inspect --format='{{.State.Health.Status}}' "${COMPOSE_PROJECT_NAME:-post_md_web}-redis-1" 2>/dev/null || true)"

  if [[ "$db_health" == "healthy" && "$redis_health" == "healthy" ]]; then
    break
  fi

  sleep 2
done

docker compose -f compose.yaml run --rm app php artisan key:generate --force
docker compose -f compose.yaml run --rm node sh -lc 'npm install'
docker compose -f compose.yaml run --rm node npm run build
docker compose -f compose.yaml run --rm app php artisan migrate --force

docker compose -f compose.yaml up -d

printf '\nBootstrap concluído.\n'
printf 'Aplicação: http://localhost:%s\n' "$APP_PORT"
printf 'Vite:      http://localhost:%s\n' "$VITE_PORT"
printf '\nExecute: make ps && make test\n'
