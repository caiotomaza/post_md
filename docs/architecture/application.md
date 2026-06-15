# Arquitetura da aplicação Laravel

## Organização inicial

Use as convenções do Laravel.

```text
src/app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   ├── Middleware/
│   └── Resources/
├── Models/
├── Policies/
├── Services/
├── Jobs/
└── Support/
```

## Controllers

- recebem request;
- autorizam;
- delegam;
- retornam view, redirect ou resource.

Não concentram regras complexas.

## Form Requests

Responsáveis por validação e autorização simples da requisição.

## Services

Criados quando uma operação possui:

- transação;
- invariantes;
- vários models;
- integração externa;
- lógica difícil de testar no controller.

## Jobs

Usar para:

- thumbnails;
- vídeo;
- importação;
- exportação;
- indexação;
- limpeza;
- e-mail.

## Policies

Toda autorização de recurso deve ser centralizada.
