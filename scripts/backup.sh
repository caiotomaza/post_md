#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
  printf 'ERRO: .env não encontrado.\n' >&2
  exit 1
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

timestamp="$(date -u +'%Y%m%dT%H%M%SZ')"
backup_dir="backups/${timestamp}"
mkdir -p "$backup_dir"

docker compose -f compose.yaml exec -T db \
  pg_dump \
  --username="${POSTGRES_USER}" \
  --dbname="${POSTGRES_DB}" \
  --format=custom \
  --no-owner \
  --no-acl \
  > "${backup_dir}/database.dump"

if [[ -d src/storage/app ]]; then
  tar -C src/storage -czf "${backup_dir}/storage-app.tar.gz" app
fi

sha256sum "${backup_dir}"/* > "${backup_dir}/SHA256SUMS"

printf 'Backup criado em %s\n' "$backup_dir"
