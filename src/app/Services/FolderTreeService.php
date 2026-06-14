<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Note;
use App\Models\Tag;

class FolderTreeService
{
    public function build(): array
    {
        $rootFolders = Folder::with(['children.children.children', 'children.notes.tags', 'notes.tags'])
            ->whereNull('parent_id')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $rootNotes = Note::with('tags')
            ->whereNull('folder_id')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return [
            'folders' => $rootFolders->map(fn ($f) => $this->formatFolder($f))->values()->all(),
            'notes' => $rootNotes->map(fn ($n) => $this->formatNote($n))->values()->all(),
        ];
    }

    public function formatFolder(Folder $folder): array
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'position' => $folder->position,
            'is_expanded' => $folder->is_expanded,
            'parent_id' => $folder->parent_id,
            'children' => $folder->children->map(fn ($f) => $this->formatFolder($f))->values()->all(),
            'notes' => $folder->notes->map(fn ($n) => $this->formatNote($n))->values()->all(),
        ];
    }

    public function formatNote(Note $note): array
    {
        return [
            'id' => $note->id,
            'name' => $note->name,
            'position' => $note->position,
            'folder_id' => $note->folder_id,
            'tags' => $note->tags->map(fn ($t) => $this->formatTag($t))->values()->all(),
        ];
    }

    public function formatTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'display_mode' => $tag->display_mode,
            'color_hex' => $tag->color_hex,
            'emoji' => $tag->emoji,
        ];
    }
}
