# post_md-web

Repositório principal do **post_md**, responsável por:

- aplicação Laravel;
- interface web;
- API HTTP usada futuramente pelo repositório mobile;
- banco PostgreSQL;
- cache, sessões e filas Redis;
- processamento assíncrono;
- armazenamento;
- importação e exportação;
- autenticação e colaboração web;
- administração e operação do servidor.

O aplicativo mobile será mantido separadamente em `post_md-mobile`.

## Estado inicial

Este pacote contém a infraestrutura e a documentação do repositório. O Laravel é instalado em `src/` pelo comando:

```bash
cp .env.example .env
make init
```

O bootstrap usa a versão Laravel definida por `LARAVEL_VERSION`.

## Requisitos

- Git;
- Docker Engine ou Docker Desktop;
- Docker Compose v2;
- Make;
- Bash.

No WSL2, mantenha o projeto no filesystem Linux:

```text
~/projetos/post_md-web
```

Evite trabalhar em `/mnt/c/...` devido ao custo de I/O de bind mounts.

## Início rápido

```bash
cp .env.example .env
make doctor
make init
```

Depois acesse:

```text
http://localhost:8000
```

O Vite estará em:

```text
http://localhost:5173
```

## Comandos principais

```bash
make help
make up
make down
make logs
make shell
make artisan CMD="about"
make composer CMD="show"
make npm CMD="run build"
make test
make quality
make backup
```

## Estrutura

```text
post_md-web/
├── src/                     # Laravel
├── docker/                  # imagens e configurações
├── scripts/                 # automação operacional
├── docs/                    # documentação exclusiva deste repositório
├── compose.yaml             # desenvolvimento
├── compose.production.yaml  # produção
├── Makefile
├── AGENTS.md
└── README.md
```

## Documentação

Comece em [`docs/README.md`](docs/README.md).

## Segurança

Nunca versione:

- `.env`;
- `APP_KEY`;
- senhas;
- tokens;
- chaves SMTP/S3/VPN;
- backups reais;
- chaves de assinatura.

Consulte [`SECURITY.md`](SECURITY.md).
