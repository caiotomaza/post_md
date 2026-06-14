<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->nullOnDelete();
            $table->string('name');
            $table->longText('content')->default('');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['folder_id', 'position']);
        });

        DB::statement(
            'CREATE UNIQUE INDEX notes_folder_name_active_unique ON notes (COALESCE(folder_id, 0), name) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
