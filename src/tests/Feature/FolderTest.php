<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_root_folder(): void
    {
        $res = $this->postJson('/folders', ['name' => 'Projetos']);
        $res->assertStatus(201)->assertJsonPath('success', true);
        $this->assertDatabaseHas('folders', ['name' => 'Projetos', 'parent_id' => null]);
    }

    public function test_create_subfolder(): void
    {
        $parent = Folder::create(['name' => 'Pai', 'position' => 0]);
        $res = $this->postJson('/folders', ['name' => 'Filho', 'parent_id' => $parent->id]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('folders', ['name' => 'Filho', 'parent_id' => $parent->id]);
    }

    public function test_folder_parent_children_relationship(): void
    {
        $parent = Folder::create(['name' => 'Pai', 'position' => 0]);
        $child = Folder::create(['name' => 'Filho', 'position' => 0, 'parent_id' => $parent->id]);

        $this->assertTrue($parent->children->contains($child));
        $this->assertTrue($child->parent->is($parent));
    }

    public function test_rename_folder(): void
    {
        $folder = Folder::create(['name' => 'Original', 'position' => 0]);
        $res = $this->patchJson("/folders/{$folder->id}", ['name' => 'Renomeada']);
        $res->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'name' => 'Renomeada']);
    }

    public function test_move_folder(): void
    {
        $target = Folder::create(['name' => 'Destino', 'position' => 0]);
        $folder = Folder::create(['name' => 'Mover', 'position' => 1]);

        $res = $this->postJson("/folders/{$folder->id}/move", ['target_parent_id' => $target->id]);
        $res->assertOk();
        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'parent_id' => $target->id]);
    }

    public function test_prevent_folder_cycle(): void
    {
        $parent = Folder::create(['name' => 'Pai', 'position' => 0]);
        $child = Folder::create(['name' => 'Filho', 'position' => 0, 'parent_id' => $parent->id]);

        $res = $this->postJson("/folders/{$parent->id}/move", ['target_parent_id' => $child->id]);
        $res->assertStatus(422);
    }

    public function test_prevent_folder_self_move(): void
    {
        $folder = Folder::create(['name' => 'Auto', 'position' => 0]);
        $res = $this->postJson("/folders/{$folder->id}/move", ['target_parent_id' => $folder->id]);
        $res->assertStatus(422);
    }

    public function test_soft_delete_folder(): void
    {
        $folder = Folder::create(['name' => 'Deletar', 'position' => 0]);
        $res = $this->deleteJson("/folders/{$folder->id}");
        $res->assertOk();
        $this->assertSoftDeleted('folders', ['id' => $folder->id]);
    }

    public function test_folder_name_is_required(): void
    {
        $res = $this->postJson('/folders', ['name' => '']);
        $res->assertStatus(422);
    }
}
