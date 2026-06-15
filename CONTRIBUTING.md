# Contribuição

## Fluxo

1. Crie branch curta a partir de `main`.
2. Faça mudanças pequenas e coerentes.
3. Adicione ou atualize testes.
4. Execute `make quality`.
5. Atualize a documentação afetada.
6. Abra pull request.

## Commits

Use mensagens como:

```text
feat(notes): cria editor markdown
fix(folders): impede ciclos na árvore
docs(docker): documenta produção
test(tags): cobre normalização de cor
```

## Banco

- migrations são incrementais;
- não altere migration já aplicada em ambientes compartilhados;
- não use `migrate:fresh` como correção;
- mudanças destrutivas exigem estratégia expand-and-contract.

## Dependências

Adicione uma dependência somente quando:

- resolve necessidade concreta;
- está mantida;
- possui licença compatível;
- não duplica recurso já existente;
- foi registrada na documentação.
