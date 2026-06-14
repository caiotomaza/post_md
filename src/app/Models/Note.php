<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = ['folder_id', 'name', 'content', 'position'];

    protected $casts = [
        'position' => 'integer',
        'folder_id' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Note $note) {
            if (!str_ends_with($note->name, '.md')) {
                $note->name = $note->name . '.md';
            }
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'note_tag')
            ->withPivot('position')
            ->orderByPivot('position')
            ->orderBy('name');
    }
}
