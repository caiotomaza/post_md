#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

required=(
  "README.md"
  "AGENTS.md"
  "docs/README.md"
  "docs/architecture/overview.md"
  "docs/development/getting-started.md"
  "docs/implementation/status.md"
  "docs/security/baseline.md"
)

failed=0

for file in "${required[@]}"; do
  if [[ ! -s "$file" ]]; then
    printf 'ERRO: documento ausente ou vazio: %s\n' "$file"
    failed=1
  fi
done

if grep -RIn --exclude-dir=spec --include='*.md' 'GavetaMD\|gavetamd' .; then
  printf 'ERRO: nome antigo encontrado na documentação.\n'
  failed=1
fi

if [[ "$failed" -eq 0 ]]; then
  printf 'Documentação validada.\n'
fi

exit "$failed"
