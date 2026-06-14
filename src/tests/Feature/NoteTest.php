<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_note(): void
    {
        $res = $this->postJson('/notes', ['name' => 'Minha nota', 'content' => '# Hello']);
        $res->assertStatus(201)->assertJsonPath('success', true);
        $this->assertDatabaseHas('notes', ['name' => 'Minha nota.md']);
    }

    public function test_md_extension_added_automatically(): void
    {
        $res = $this->postJson('/notes', ['name' => 'Sem extensão']);
        $res->assertStatus(201);
        $this->assertDatabaseHas('notes', ['name' => 'Sem extensão.md']);
    }

    public function test_md_extension_not_duplicated(): void
    {
        $res = $this->postJson('/notes', ['name' => 'Com extensão.md']);
        $res->assertStatus(201);
        $this->assertDatabaseHas('notes', ['name' => 'Com extensão.md']);
        $this->assertEquals(1, Note::where('name', 'Com extensão.md')->count());
    }

    public function test_prevent_duplicate_name_in_same_folder(): void
    {
        $folder = Folder::create(['name' => 'Pasta', 'position' => 0]);
        Note::create(['name' => 'Duplicada.md', 'folder_id' => $folder->id, 'content' => '', 'position' => 0]);

        $res = $this->postJson('/notes', ['name' => 'Duplicada', 'folder_id' => $folder->id]);
        $res->assertStatus(422);
    }

    public function test_allow_reusing_note_name_after_soft_delete(): void
    {
        $folder = Folder::create(['name' => 'Pasta', 'position' => 0]);
        $note = Note::create(['name' => 'Reutilizavel.md', 'folder_id' => $folder->id, 'content' => '', 'position' => 0]);

        $note->delete();

        $res = $this->postJson('/notes', ['name' => 'Reutilizavel', 'folder_id' => $folder->id]);
        $res->assertStatus(201);
        $this->assertSame(1, Note::where('name', 'Reutilizavel.md')->where('folder_id', $folder->id)->count());
    }

    public function test_allow_same_name_in_different_folders(): void
    {
        $f1 = Folder::create(['name' => 'Pasta A', 'position' => 0]);
        $f2 = Folder::create(['name' => 'Pasta B', 'position' => 1]);
        Note::create(['name' => 'Nota.md', 'folder_id' => $f1->id, 'content' => '', 'position' => 0]);

        $res = $this->postJson('/notes', ['name' => 'Nota', 'folder_id' => $f2->id]);
        $res->assertStatus(201);
    }

    public function test_update_note_content(): void
    {
        $note = Note::create(['name' => 'Teste.md', 'content' => 'original', 'position' => 0]);
        $res = $this->patchJson("/notes/{$note->id}", ['content' => '# Novo conteúdo']);
        $res->assertOk();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'content' => '# Novo conteúdo']);
    }

    public function test_move_note_to_folder(): void
    {
        $folder = Folder::create(['name' => 'Destino', 'position' => 0]);
        $note = Note::create(['name' => 'Nota.md', 'content' => '', 'position' => 0]);

        $res = $this->postJson("/notes/{$note->id}/move", ['target_folder_id' => $folder->id]);
        $res->assertOk();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'folder_id' => $folder->id]);
    }

    public function test_move_note_to_root(): void
    {
        $folder = Folder::create(['name' => 'Pasta', 'position' => 0]);
        $note = Note::create(['name' => 'Nota.md', 'folder_id' => $folder->id, 'content' => '', 'position' => 0]);

        $res = $this->postJson("/notes/{$note->id}/move", ['target_folder_id' => null]);
        $res->assertOk();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'folder_id' => null]);
    }

    public function test_soft_delete_note(): void
    {
        $note = Note::create(['name' => 'Apagar.md', 'content' => '', 'position' => 0]);
        $res = $this->deleteJson("/notes/{$note->id}");
        $res->assertOk();
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_show_note(): void
    {
        $note = Note::create(['name' => 'Leitura.md', 'content' => '# Conteúdo', 'position' => 0]);
        $res = $this->getJson("/notes/{$note->id}");
        $res->assertOk()->assertJsonPath('data.name', 'Leitura.md');
    }
}
