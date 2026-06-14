<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Note;

class MoveNoteService
{
    public function move(Note $note, ?int $targetFolderId): void
    {
        if ($targetFolderId !== null) {
            Folder::findOrFail($targetFolderId);
        }

        $note->update(['folder_id' => $targetFolderId]);
    }
}
