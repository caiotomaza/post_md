# Baseline de segurança

## Desenvolvimento

- banco e Redis sem portas públicas;
- portas expostas somente em loopback;
- secrets no `.env`;
- sem Xdebug por padrão;
- PHP não root;
- uploads limitados;
- arquivos ocultos bloqueados pelo Nginx.

## Produção

- TLS;
- `APP_DEBUG=false`;
- cookies seguros;
- trusted proxies;
- CSP;
- rate limiting;
- sanitização Markdown;
- validação de MIME e magic bytes;
- URLs temporárias;
- storage privado;
- logs redigidos;
- backup criptografado;
- imagens fixadas por versão ou digest.

## Supply chain

- lockfiles;
- Dependabot;
- CI;
- revisão de dependências;
- ações GitHub atualizadas;
- SBOM em releases futuras.
