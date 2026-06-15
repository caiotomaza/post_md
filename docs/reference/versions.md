# Baseline de versões

Verificada em junho de 2026.

| Componente | Baseline |
|---|---|
| Laravel | 13.x |
| PHP | 8.4 |
| Composer | 2.8 |
| Node | 24 LTS, Krypton |
| PostgreSQL | 18.4 |
| Redis | 8.6.4 |
| Nginx | 1.30.2 |
| Docker Compose | Specification / v2 |

## Política

- versões de aplicação ficam em lockfiles;
- imagens possuem versão explícita;
- atualizações passam por CI;
- pré-releases não são usadas;
- PostgreSQL major upgrade exige procedimento próprio.
