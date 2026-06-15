#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env.production ]]; then
  printf 'ERRO: .env.production não encontrado.\n' >&2
  exit 1
fi

compose=(docker compose --env-file .env.production -f compose.production.yaml)

"${compose[@]}" config >/dev/null
"${compose[@]}" build --pull
"${compose[@]}" up -d db redis
"${compose[@]}" run --rm app php artisan migrate --force
"${compose[@]}" up -d
"${compose[@]}" ps

printf 'Deploy concluído. Verifique health checks e logs.\n'
