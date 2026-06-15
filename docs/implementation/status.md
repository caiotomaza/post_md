# Estado da implementação

```yaml
repository: post_md-web
phase: repository-bootstrap
status: ready_for_initialization
laravel_installed: false
mobile_in_this_repository: false
```

## Concluído

- estrutura do repositório;
- Docker de desenvolvimento;
- Docker de produção;
- scripts;
- CI inicial;
- documentação;
- separação formal do mobile.

## Próxima ação

```bash
cp .env.example .env
make doctor
make init
```

Depois:

1. validar Laravel;
2. configurar interface base;
3. implementar pastas;
4. implementar notas Markdown;
5. implementar tags.
