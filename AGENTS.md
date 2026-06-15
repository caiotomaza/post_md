# Instruções permanentes para agentes

## Projeto

Este é o repositório `post_md-web`.

O aplicativo mobile pertence a outro repositório e não deve ser criado aqui.

## Estrutura

- Laravel: `src/`
- Docker: `docker/`
- documentação: `docs/`
- scripts: `scripts/`

Não mover o Laravel para a raiz.

## Antes de alterar

1. Leia `README.md`.
2. Leia `docs/README.md`.
3. Leia `docs/implementation/status.md`.
4. Execute `git status --short --branch`.
5. Preserve alterações válidas.

## Proibido sem autorização

- `git reset --hard`;
- `git clean -fd`;
- `docker compose down -v`;
- `php artisan migrate:fresh`;
- `php artisan db:wipe`;
- apagar volumes ou backups;
- fazer push;
- criar código mobile.

## Engenharia

- respeitar convenções do Laravel;
- controllers finos;
- Form Requests;
- Policies;
- transações para invariantes;
- jobs para tarefas pesadas;
- testes para correções e funcionalidades;
- migrations incrementais;
- OpenAPI quando a API mobile começar;
- não criar microserviços;
- não criar abstrações sem uso real.

## Docker

- desenvolvimento: `compose.yaml`;
- produção: `compose.production.yaml`;
- PostgreSQL e Redis sem portas públicas;
- mesma imagem PHP para app, worker e scheduler;
- build de produção reproduzível;
- runtime não root.

## Conclusão de tarefa

Uma tarefa exige:

- código;
- testes;
- build;
- documentação afetada;
- atualização de `docs/implementation/status.md`;
- resumo de comandos e resultados.
