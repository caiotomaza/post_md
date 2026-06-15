#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

file="${1:-}"
confirmation="${2:-}"

if [[ -z "$file" || ! -f "$file" ]]; then
  printf 'Uso: make restore FILE=backups/.../database.dump CONFIRM=restore\n' >&2
  exit 1
fi

if [[ "$confirmation" != "restore" ]]; then
  printf 'ERRO: restauração é destrutiva. Use CONFIRM=restore.\n' >&2
  exit 1
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

docker compose -f compose.yaml exec -T db \
  pg_restore \
  --username="${POSTGRES_USER}" \
  --dbname="${POSTGRES_DB}" \
  --clean \
  --if-exists \
  --no-owner \
  --no-acl \
  < "$file"

printf 'Restauração concluída: %s\n' "$file"
