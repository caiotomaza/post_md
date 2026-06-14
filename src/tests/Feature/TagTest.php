<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_color_tag(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'Urgente',
            'display_mode' => 'color',
            'color_hex' => '#FF0000',
        ]);
        $res->assertStatus(201)->assertJsonPath('success', true);
        $this->assertDatabaseHas('tags', ['name' => 'Urgente', 'color_hex' => '#FF0000']);
    }

    public function test_create_emoji_tag(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'Docs',
            'display_mode' => 'emoji',
            'emoji' => '📚',
        ]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('tags', ['name' => 'Docs', 'emoji' => '📚']);
    }

    public function test_create_both_tag(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'Projeto',
            'display_mode' => 'both',
            'color_hex' => '#8B7CFF',
            'emoji' => '🧩',
        ]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('tags', ['name' => 'Projeto', 'color_hex' => '#8B7CFF', 'emoji' => '🧩']);
    }

    public function test_normalize_short_hex(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'Curto',
            'display_mode' => 'color',
            'color_hex' => '#F00',
        ]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('tags', ['color_hex' => '#FF0000']);
    }

    public function test_reject_invalid_hex(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'Inválido',
            'display_mode' => 'color',
            'color_hex' => 'red',
        ]);
        $res->assertStatus(422);
    }

    public function test_reject_hex_with_alpha(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'Alpha',
            'display_mode' => 'color',
            'color_hex' => '#FF000088',
        ]);
        $res->assertStatus(422);
    }

    public function test_color_required_for_color_mode(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'SemCor',
            'display_mode' => 'color',
        ]);
        $res->assertStatus(422);
    }

    public function test_emoji_required_for_emoji_mode(): void
    {
        $res = $this->postJson('/tags', [
            'name' => 'SemEmoji',
            'display_mode' => 'emoji',
        ]);
        $res->assertStatus(422);
    }

    public function test_attach_tag_to_note(): void
    {
        $note = Note::create(['name' => 'Nota.md', 'content' => '', 'position' => 0]);
        $tag = Tag::create(['name' => 'Tag', 'display_mode' => 'color', 'color_hex' => '#123456', 'position' => 0]);

        $res = $this->postJson("/notes/{$note->id}/tags/{$tag->id}");
        $res->assertOk();
        $this->assertDatabaseHas('note_tag', ['note_id' => $note->id, 'tag_id' => $tag->id]);
    }

    public function test_detach_tag_from_note(): void
    {
        $note = Note::create(['name' => 'Nota.md', 'content' => '', 'position' => 0]);
        $tag = Tag::create(['name' => 'Tag', 'display_mode' => 'color', 'color_hex' => '#123456', 'position' => 0]);
        $note->tags()->attach($tag->id, ['position' => 0]);

        $res = $this->deleteJson("/notes/{$note->id}/tags/{$tag->id}");
        $res->assertOk();
        $this->assertDatabaseMissing('note_tag', ['note_id' => $note->id, 'tag_id' => $tag->id]);
    }

    public function test_delete_tag(): void
    {
        $tag = Tag::create(['name' => 'Excluir', 'display_mode' => 'color', 'color_hex' => '#ABCDEF', 'position' => 0]);
        $res = $this->deleteJson("/tags/{$tag->id}");
        $res->assertOk();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_tag_name_must_be_unique(): void
    {
        Tag::create(['name' => 'Único', 'display_mode' => 'color', 'color_hex' => '#111111', 'position' => 0]);
        $res = $this->postJson('/tags', ['name' => 'Único', 'display_mode' => 'color', 'color_hex' => '#222222']);
        $res->assertStatus(422);
    }
}
