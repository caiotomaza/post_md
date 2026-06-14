<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveNoteRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Services\FolderTreeService;
use App\Services\MoveNoteService;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function __construct(
        private FolderTreeService $treeService,
        private MoveNoteService $moveService,
    ) {}

    public function show(Note $note): JsonResponse
    {
        $note->load('tags', 'folder');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $note->id,
                'name' => $note->name,
                'content' => $note->content,
                'folder_id' => $note->folder_id,
                'folder_name' => $note->folder?->name,
                'position' => $note->position,
                'tags' => $note->tags->map(fn ($t) => $this->treeService->formatTag($t))->values(),
                'updated_at' => $note->updated_at?->toISOString(),
            ],
        ]);
    }

    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = Note::create([
            'name' => $request->input('name'),
            'folder_id' => $request->input('folder_id'),
            'content' => $request->input('content', ''),
            'position' => $request->input('position', 0),
        ]);

        $note->load('tags', 'folder');

        return response()->json([
            'success' => true,
            'message' => 'Nota criada.',
            'data' => $this->treeService->formatNote($note),
        ], 201);
    }

    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        $note->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Nota salva.',
            'data' => $this->treeService->formatNote($note->load('tags')),
        ]);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota excluída.',
        ]);
    }

    public function move(MoveNoteRequest $request, Note $note): JsonResponse
    {
        $this->moveService->move($note, $request->input('target_folder_id'));

        return response()->json([
            'success' => true,
            'message' => 'Nota movida.',
            'data' => $this->treeService->build(),
        ]);
    }
}
