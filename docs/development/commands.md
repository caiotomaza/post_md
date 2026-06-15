# Comandos

## Ambiente

```bash
make up
make down
make restart
make ps
make logs
```

`make down` não apaga volumes.

## Laravel

```bash
make shell
make artisan CMD="about"
make migrate
make seed
make cache-clear
```

## Dependências

```bash
make composer CMD="require pacote"
make npm CMD="install pacote"
```

## Qualidade

```bash
make test
make quality
make docs-check
```

## Banco e Redis no host

Somente quando necessário:

```bash
docker compose -f compose.yaml -f compose.tools.yaml up -d
```

As portas ficam limitadas a `127.0.0.1`.
