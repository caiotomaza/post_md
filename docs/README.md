# Documentação do post_md-web

Esta pasta documenta exclusivamente o repositório principal Laravel e web.

O aplicativo mobile pertence ao repositório separado `post_md-mobile`.

## Ordem de leitura

1. [Escopo do repositório](product/scope.md)
2. [Arquitetura](architecture/overview.md)
3. [Estrutura](architecture/repository-structure.md)
4. [Docker](architecture/docker.md)
5. [Primeiros passos](development/getting-started.md)
6. [Comandos](development/commands.md)
7. [Status](implementation/status.md)
8. [Roadmap](implementation/roadmap.md)
9. [Segurança](security/baseline.md)

## Estrutura

```text
docs/
├── product/
├── architecture/
├── development/
├── operations/
├── security/
├── adr/
├── implementation/
└── reference/
```

## Regra

Código, testes e documentação devem mudar juntos.

Decisões difíceis de reverter devem ser registradas em ADR.
