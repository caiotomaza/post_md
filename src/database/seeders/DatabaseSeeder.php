<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTags();
        $this->seedFoldersAndNotes();
    }

    private function seedTags(): void
    {
        $tags = [
            [
                'name' => 'Importante',
                'display_mode' => 'color',
                'color_hex' => '#E25C5C',
                'emoji' => null,
                'position' => 0,
            ],
            [
                'name' => 'Documentação',
                'display_mode' => 'emoji',
                'color_hex' => null,
                'emoji' => '📚',
                'position' => 1,
            ],
            [
                'name' => 'Projeto',
                'display_mode' => 'both',
                'color_hex' => '#8B7CFF',
                'emoji' => '🧩',
                'position' => 2,
            ],
        ];

        foreach ($tags as $tagData) {
            Tag::updateOrCreate(['name' => $tagData['name']], $tagData);
        }
    }

    private function seedFoldersAndNotes(): void
    {
        $bemVindo = Folder::firstOrCreate(
            ['name' => 'Bem-vindo', 'parent_id' => null],
            ['position' => 0, 'is_expanded' => true]
        );
        $projetos = Folder::firstOrCreate(
            ['name' => 'Projetos', 'parent_id' => null],
            ['position' => 1, 'is_expanded' => true]
        );
        Folder::firstOrCreate(
            ['name' => 'Anotações', 'parent_id' => null],
            ['position' => 2, 'is_expanded' => false]
        );

        $welcomeNote = Note::firstOrCreate(
            ['folder_id' => $bemVindo->id, 'name' => 'Bem-vindo ao post_md.md'],
            ['content' => $this->welcomeContent(), 'position' => 0]
        );

        $exampleNote = Note::firstOrCreate(
            ['folder_id' => $projetos->id, 'name' => 'Exemplo de projeto.md'],
            ['content' => $this->exampleProjectContent(), 'position' => 0]
        );

        $docTag = Tag::where('name', 'Documentação')->first();
        $projetoTag = Tag::where('name', 'Projeto')->first();

        if ($docTag) {
            $welcomeNote->tags()->syncWithoutDetaching([$docTag->id => ['position' => 0]]);
        }
        if ($projetoTag) {
            $exampleNote->tags()->syncWithoutDetaching([$projetoTag->id => ['position' => 0]]);
        }
    }

    private function welcomeContent(): string
    {
        return <<<'MD'
# Bem-vindo ao post_md

**post_md** é uma ferramenta de notas em Markdown organizada por pastas.

## Funcionalidades

- Pastas e subpastas
- Notas em Markdown (`.md`)
- Tags com cor e/ou emoji
- Abas para navegar entre notas abertas
- Editor com toolbar e visualização renderizada
- Autosave automático
- Dark mode e light mode

## Como usar

1. **Crie uma pasta** usando o ícone na barra da árvore
2. **Crie uma nota** dentro de uma pasta ou na raiz
3. **Edite** em modo Fonte ou leia em modo Leitura
4. As alterações são **salvas automaticamente**

## Atalhos da toolbar

Use a toolbar do editor para formatar rapidamente:

| Ação | Resultado |
|------|-----------|
| **B** | **negrito** |
| *I* | *itálico* |
| ~~S~~ | ~~tachado~~ |
| H | # Título |
| `code` | `código inline` |

> Selecione texto antes de clicar para envolver a seleção.

---

Boas anotações! 🚀
MD;
    }

    private function exampleProjectContent(): string
    {
        return <<<'MD'
# Exemplo de projeto

Use esta nota para documentar seu projeto.

## Objetivo

Descreva o objetivo principal aqui.

## Tarefas

- [ ] Definir escopo
- [ ] Criar estrutura
- [ ] Implementar funcionalidades
- [x] Criar nota de exemplo

## Links úteis

- [Documentação do Laravel](https://laravel.com/docs)
- [Guia Markdown](https://www.markdownguide.org)

## Código de exemplo

```php
<?php

echo "Hello, post_md!";
```

---

*Editado com post_md*
MD;
    }
}
