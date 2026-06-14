<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_root_contains_post_md_name(): void
    {
        $this->get('/')->assertSee('post_md');
    }

    public function test_root_has_no_login_redirect(): void
    {
        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertDontSee('login');
    }

    public function test_folder_tree_endpoint(): void
    {
        Folder::create(['name' => 'Teste', 'position' => 0]);
        $res = $this->getJson('/folders/tree');
        $res->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['folders', 'notes']]);
    }

    public function test_tags_endpoint_returns_list(): void
    {
        Tag::create(['name' => 'MyTag', 'display_mode' => 'color', 'color_hex' => '#AABBCC', 'position' => 0]);
        $res = $this->getJson('/tags');
        $res->assertOk()->assertJsonPath('success', true);
    }

    public function test_json_responses_have_success_key(): void
    {
        $res = $this->postJson('/folders', ['name' => 'JsonFolder']);
        $res->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_validation_error_returns_422(): void
    {
        $res = $this->postJson('/folders', ['name' => '']);
        $res->assertStatus(422);
    }

    public function test_note_autosave_endpoint(): void
    {
        $note = Note::create(['name' => 'Autosave.md', 'content' => 'Antigo', 'position' => 0]);
        $res = $this->patchJson("/notes/{$note->id}", ['content' => 'Novo conteúdo']);
        $res->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'content' => 'Novo conteúdo']);
    }
}
