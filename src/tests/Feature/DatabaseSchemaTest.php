<?php

namespace Tests\Feature;

use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_tables_are_created_by_migrations(): void
    {
        foreach (['folders', 'notes', 'tags', 'note_tag'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected table [{$table}] to exist.");
        }
    }

    public function test_folders_table_has_required_columns(): void
    {
        foreach (['id', 'parent_id', 'name', 'position', 'is_expanded', 'created_at', 'updated_at', 'deleted_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('folders', $column), "Expected folders.{$column} to exist.");
        }
    }

    public function test_hard_deleting_parent_folder_nulls_child_parent_id(): void
    {
        $parent = Folder::create(['name' => 'Pai', 'position' => 0]);
        $child = Folder::create(['name' => 'Filho', 'position' => 0, 'parent_id' => $parent->id]);

        $parent->forceDelete();

        $this->assertNull($child->refresh()->parent_id);
    }
}
