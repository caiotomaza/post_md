# Segurança

## Reporte

Não abra vulnerabilidades com dados sensíveis em issue pública.

Use um canal privado definido pelo proprietário do repositório antes da publicação pública.

## Baseline

- secrets fora do Git;
- banco e Redis em rede interna;
- TLS em produção;
- validação de uploads;
- sanitização de Markdown;
- autorização no backend;
- logs sem conteúdo das notas;
- backups criptografados fora do host;
- dependências e imagens atualizadas;
- CI com análise de dependências.

## Produção

Não use as senhas de exemplo.

Gere:

```bash
openssl rand -base64 32
```

Use secret manager quando disponível.
