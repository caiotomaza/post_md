# Estrutura do repositório

```text
post_md-web/
├── src/                     # código Laravel
├── docker/
│   ├── php/
│   ├── nginx/
│   ├── postgres/
│   └── laravel/
├── scripts/
├── docs/
├── .github/
├── compose.yaml
├── compose.production.yaml
├── compose.tools.yaml
├── Makefile
├── AGENTS.md
└── README.md
```

## `src/`

É um projeto Laravel padrão.

Não reorganizar o framework em uma estrutura não convencional sem benefício comprovado.

## `docker/`

Somente artefatos de runtime:

- Dockerfile;
- PHP;
- PHP-FPM;
- Nginx;
- inicialização PostgreSQL;
- template de ambiente Laravel.

## `scripts/`

Automação operacional, não regra de negócio.

## `docs/`

Decisões, operação, segurança e estado.

## `compose.tools.yaml`

Override opcional para expor PostgreSQL e Redis em loopback.

Nunca utilizar em produção.
