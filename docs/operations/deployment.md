# Deploy

## Pré-condições

- `src/composer.lock`;
- `src/package-lock.json`;
- `.env.production`;
- backup válido;
- domínio e TLS externos;
- imagem testada.

## Processo

```bash
cp .env.production.example .env.production
```

Preencha secrets e gere `APP_KEY`.

```bash
./scripts/production-deploy.sh
```

O script:

1. valida Compose;
2. constrói imagens;
3. sobe banco e Redis;
4. executa migrations;
5. sobe aplicação;
6. exibe status.

## Reverse proxy

Em produção real, prefira publicar o serviço web apenas para:

- Caddy;
- Nginx Proxy Manager;
- Traefik;
- load balancer.

O proxy externo é responsável por TLS.

## Rollback

- mantenha tag anterior da imagem;
- não dependa de migration reversível;
- use expand-and-contract;
- valide banco antes de retornar a versão.
