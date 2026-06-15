# Primeiros passos

## 1. Preparar

```bash
cp .env.example .env
```

Ajuste UID/GID:

```bash
id -u
id -g
```

## 2. Diagnóstico

```bash
make doctor
```

## 3. Instalar

```bash
make init
```

O comando:

1. constrói PHP;
2. instala Laravel em `src/`;
3. cria `src/.env`;
4. gera APP_KEY;
5. instala npm;
6. compila assets;
7. sobe PostgreSQL e Redis;
8. executa migrations;
9. sobe todos os serviços.

## 4. Abrir

```text
http://localhost:8000
```

## 5. Validar

```bash
make ps
make test
make quality
```

## WSL2

Use:

```text
~/projetos/post_md-web
```

Evite:

```text
/mnt/c/Users/...
```
