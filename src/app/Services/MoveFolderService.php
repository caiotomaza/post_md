<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Validation\ValidationException;

class MoveFolderService
{
    public function move(Folder $folder, ?int $targetParentId): void
    {
        if ($targetParentId === null) {
            $folder->update(['parent_id' => null]);
            return;
        }

        if ($targetParentId === $folder->id) {
            throw ValidationException::withMessages([
                'target_parent_id' => 'Uma pasta não pode ser filha de si mesma.',
            ]);
        }

        $target = Folder::findOrFail($targetParentId);

        if ($target->isDescendantOf($folder->id)) {
            throw ValidationException::withMessages([
                'target_parent_id' => 'Não é possível mover uma pasta para dentro de sua própria subpasta.',
            ]);
        }

        $folder->update(['parent_id' => $targetParentId]);
    }
}
