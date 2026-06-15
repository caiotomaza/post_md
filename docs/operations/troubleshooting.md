# Troubleshooting

## Laravel não existe

```bash
make init
```

## Tabela não existe

```bash
make artisan CMD="migrate:status"
make migrate
```

Não use `migrate:fresh` para corrigir ambiente compartilhado.

## Permissões

Confirme `APP_UID` e `APP_GID` no `.env`.

Não use:

```bash
chmod -R 777
```

## PostgreSQL

```bash
docker compose exec db psql -U post_md -d post_md
```

## Redis

```bash
docker compose exec redis redis-cli -a "$REDIS_PASSWORD" ping
```

## Vite

```bash
docker compose logs node
make npm CMD="install"
make npm CMD="run build"
```

## Health

```bash
docker compose ps
curl http://localhost:8000/nginx-health
```
