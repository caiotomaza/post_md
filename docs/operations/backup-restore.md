# Backup e restauração

## Backup local

```bash
make backup
```

Gera:

```text
backups/<timestamp>/
├── database.dump
├── storage-app.tar.gz
└── SHA256SUMS
```

## Restore

```bash
make restore FILE=backups/.../database.dump CONFIRM=restore
```

A restauração é destrutiva.

## Produção

Backups precisam:

- destino fora do host;
- criptografia;
- retenção;
- monitoramento;
- teste periódico de restauração;
- banco e storage do mesmo ponto lógico.

Exportação de usuário não substitui backup operacional.
