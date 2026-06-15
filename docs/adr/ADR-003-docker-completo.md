# ADR-003 — Docker para desenvolvimento e produção

```yaml
status: aceita
date: 2026-06-14
```

## Decisão

Containerizar:

- PHP-FPM;
- Nginx;
- PostgreSQL;
- Redis;
- Node;
- worker;
- scheduler.

Usar arquivos separados para desenvolvimento e produção.

## Motivo

- ambiente reproduzível;
- onboarding simples;
- paridade;
- isolamento;
- operação documentada.

## Consequência

O projeto depende de Docker Compose para o fluxo oficial.
