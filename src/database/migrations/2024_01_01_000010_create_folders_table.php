<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('folders')
                ->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_expanded')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['parent_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
