# Arquitetura Docker

## Desenvolvimento

Arquivo:

```text
compose.yaml
```

Serviços:

- app;
- web;
- node;
- db;
- redis;
- worker;
- scheduler.

O código é montado de `src/` em `/var/www/html`.

## Produção

Arquivo:

```text
compose.production.yaml
```

Características:

- imagem PHP imutável;
- dependências Composer sem desenvolvimento;
- assets compilados;
- sem bind mount do código;
- app não root;
- banco e Redis internos;
- storage em volume;
- migrations executadas pelo deploy, não pelo entrypoint.

## Redes

### `edge`

- web;
- node em desenvolvimento.

### `backend`

- app;
- worker;
- scheduler;
- web;
- PostgreSQL;
- Redis.

`backend` é interna.

## PostgreSQL 18

O volume é montado em:

```text
/var/lib/postgresql
```

`PGDATA`:

```text
/var/lib/postgresql/18/docker
```

## Health checks

- PostgreSQL: `pg_isready`;
- Redis: `redis-cli ping`;
- PHP-FPM: endpoint `/ping`;
- Nginx: `/nginx-health`.
