#!/usr/bin/env bash
set -Eeuo pipefail

fail=0

check() {
  local command_name="$1"

  if command -v "$command_name" >/dev/null 2>&1; then
    printf 'OK  %s\n' "$command_name"
  else
    printf 'ERRO  %s não encontrado\n' "$command_name"
    fail=1
  fi
}

check git
check docker
check bash
check make

if docker compose version >/dev/null 2>&1; then
  printf 'OK  docker compose\n'
else
  printf 'ERRO  Docker Compose v2 não encontrado\n'
  fail=1
fi

if [[ "$(uname -s)" == "Linux" ]] && grep -qi microsoft /proc/version 2>/dev/null; then
  case "$(pwd)" in
    /mnt/*)
      printf 'AVISO  No WSL2, mova o projeto para ~/projetos para melhorar I/O.\n'
      ;;
  esac
fi

exit "$fail"
