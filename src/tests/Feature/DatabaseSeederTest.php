<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(3, Folder::count());
        $this->assertSame(2, Note::count());
        $this->assertSame(3, Tag::count());
        $this->assertSame(2, DB::table('note_tag')->count());

        $this->assertDatabaseHas('folders', ['name' => 'Bem-vindo', 'parent_id' => null]);
        $this->assertDatabaseHas('folders', ['name' => 'Projetos', 'parent_id' => null]);
        $this->assertDatabaseHas('folders', ['name' => 'Anotações', 'parent_id' => null]);
        $this->assertDatabaseHas('notes', ['name' => 'Bem-vindo ao post_md.md']);
        $this->assertDatabaseHas('notes', ['name' => 'Exemplo de projeto.md']);
    }
}
