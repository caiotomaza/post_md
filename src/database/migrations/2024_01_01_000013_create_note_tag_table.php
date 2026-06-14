<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_tag', function (Blueprint $table) {
            $table->foreignId('note_id')
                ->constrained('notes')
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained('tags')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['note_id', 'tag_id']);
            $table->index(['note_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_tag');
    }
};
