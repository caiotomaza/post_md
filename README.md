# post_md

Ferramenta de notas em Markdown com organização por pastas, tags e visualização renderizada. Sem login, sem usuários — acesso direto à interface principal.

## Etapa atual: base funcional

Esta versão implementa:

- Pastas e subpastas
- Notas `.md` com editor e visualização Markdown
- Tags com cor, emoji ou ambos
- Abas web com persistência no localStorage
- Autosave automático (debounce ~850ms)
- Dark mode / Light mode
- PostgreSQL para persistência dos dados

## Estrutura

```
POST_MD/
├── docker/
│   ├── nginx/default.conf
│   └── php/Dockerfile
├── src/                    # Laravel 12
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Http/Requests/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── resources/
│   │   ├── css/app.css
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── workspace.js
│   │   │   ├── editor.js
│   │   │   ├── tabs.js
│   │   │   └── tags.js
│   │   └── views/
│   │       ├── layouts/app.blade.php
│   │       └── workspace/index.blade.php
│   └── routes/web.php
└── docker-compose.yml
```

## Como subir com Docker

```bash
# 1. Clonar / entrar na pasta
cd POST_MD

# 2. Build e start
docker compose up -d --build

# 3. Se vendor/ não existir, instalar dependências PHP uma vez
# docker compose exec app composer install

# 4. Garantir config limpa, migrations e dados iniciais
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed

# 5. Instalar dependências Node e compilar assets
docker compose exec app npm install
docker compose exec app npm run build

# 6. Acessar
# http://localhost:8000
```

## Configurar .env

O arquivo `src/.env` já está configurado para o Docker:

```dotenv
APP_NAME=post_md
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=post_md_db
DB_USERNAME=post_md_user
DB_PASSWORD=secret_password
```

## Executar migrations

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan migrate
```

## Executar seeds

Popula com pastas e tags iniciais (só quando as tabelas estão vazias):

```bash
docker compose exec app php artisan db:seed
```

## Compilar Vite

```bash
# Build de produção
docker compose exec app npm run build

# Watch (desenvolvimento)
docker compose exec app npm run dev
```

## Executar testes

Os testes usam SQLite in-memory (sem necessidade do PostgreSQL):

```bash
docker compose exec app php artisan test
```

## Acessar

```
http://localhost:8000
```

Sem login. A interface abre diretamente.

## Rotas disponíveis

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | / | Interface principal |
| GET | /folders/tree | Árvore completa |
| POST | /folders | Criar pasta |
| PATCH | /folders/{id} | Atualizar pasta |
| DELETE | /folders/{id} | Excluir pasta |
| POST | /folders/{id}/move | Mover pasta |
| POST | /folders/{id}/toggle | Alternar expandido |
| POST | /notes | Criar nota |
| GET | /notes/{id} | Carregar nota |
| PATCH | /notes/{id} | Atualizar nota (autosave) |
| DELETE | /notes/{id} | Excluir nota |
| POST | /notes/{id}/move | Mover nota |
| GET | /tags | Listar tags |
| POST | /tags | Criar tag |
| PATCH | /tags/{id} | Atualizar tag |
| DELETE | /tags/{id} | Excluir tag |
| POST | /notes/{note}/tags/{tag} | Associar tag |
| DELETE | /notes/{note}/tags/{tag} | Remover tag |

## Limitações desta etapa

- Sem autenticação ou usuários
- Sem compartilhamento
- Sem aplicativo Android
- Sem importação/exportação
- Sem busca full-text (visual apenas)
- Sem drag and drop na árvore
- Sem Redis, filas ou WebSockets
- Editor baseado em `textarea` (CodeMirror pode ser integrado futuramente)

## Próximas etapas sugeridas

- Busca full-text nas notas
- Drag and drop para reorganizar itens
- Integração com CodeMirror 6 para syntax highlight
- Histórico de versões das notas
- Exportação para PDF / HTML
- Autenticação e múltiplos usuários
- Compartilhamento de notas
