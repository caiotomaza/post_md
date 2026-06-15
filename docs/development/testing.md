# Testes

## Estratégia

- unitários para regras puras;
- feature para HTTP, banco e autorização;
- integração para storage, Redis e jobs;
- browser/E2E quando os fluxos visuais estabilizarem.

## Banco

Testes devem usar banco separado.

Não usar o banco de desenvolvimento.

## Correções

Todo bug deve receber teste de regressão quando tecnicamente possível.

## Comando

```bash
make test
```

Gate completo:

```bash
make quality
```
