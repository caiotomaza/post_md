# ADR-001 — Separar web e mobile

```yaml
status: aceita
date: 2026-06-14
```

## Decisão

Manter:

- `post_md-web`;
- `post_md-mobile`.

## Motivo

- ciclos de release independentes;
- stacks diferentes;
- CI menor;
- responsabilidades claras;
- mobile não interfere no bootstrap web.

## Consequência

O contrato HTTP precisa ser versionado quando o mobile começar.
