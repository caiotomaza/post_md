<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = ['parent_id', 'name', 'position', 'is_expanded'];

    protected $casts = [
        'is_expanded' => 'boolean',
        'position' => 'integer',
        'parent_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id')
            ->orderBy('position')
            ->orderBy('name');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)
            ->orderBy('position')
            ->orderBy('name');
    }

    public function isDescendantOf(int $folderId): bool
    {
        $current = $this;
        while ($current->parent_id !== null) {
            if ($current->parent_id === $folderId) {
                return true;
            }
            $current = $current->parent;
            if ($current === null) {
                break;
            }
        }
        return false;
    }
}
