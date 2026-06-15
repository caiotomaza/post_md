# Variáveis de ambiente

## Raiz

O `.env` da raiz configura Docker Compose.

Principais grupos:

- portas;
- UID/GID;
- imagens;
- PostgreSQL;
- Redis;
- versão do Laravel.

## Laravel

`src/.env` configura a aplicação.

Ele é criado por `make init`.

## Produção

`.env.production` configura Compose e Laravel por variáveis injetadas.

Não incluir secrets na imagem.

## Prioridade

- desenvolvimento: `src/.env`;
- produção: environment do container;
- valores versionados: somente exemplos sem segredo.
