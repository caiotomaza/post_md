<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveFolderRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use App\Services\FolderTreeService;
use App\Services\MoveFolderService;
use Illuminate\Http\JsonResponse;

class FolderController extends Controller
{
    public function __construct(
        private FolderTreeService $treeService,
        private MoveFolderService $moveService,
    ) {}

    public function tree(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->treeService->build(),
        ]);
    }

    public function store(StoreFolderRequest $request): JsonResponse
    {
        $folder = Folder::create([
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_id'),
            'position' => $request->input('position', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasta criada.',
            'data' => $this->treeService->formatFolder($folder->load('children', 'notes')),
        ], 201);
    }

    public function update(UpdateFolderRequest $request, Folder $folder): JsonResponse
    {
        $folder->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pasta atualizada.',
            'data' => $this->treeService->formatFolder($folder->load('children', 'notes.tags')),
        ]);
    }

    public function destroy(Folder $folder): JsonResponse
    {
        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pasta excluída.',
        ]);
    }

    public function move(MoveFolderRequest $request, Folder $folder): JsonResponse
    {
        $this->moveService->move($folder, $request->input('target_parent_id'));

        return response()->json([
            'success' => true,
            'message' => 'Pasta movida.',
            'data' => $this->treeService->build(),
        ]);
    }

    public function toggle(Folder $folder): JsonResponse
    {
        $folder->update(['is_expanded' => !$folder->is_expanded]);

        return response()->json([
            'success' => true,
            'data' => ['is_expanded' => $folder->is_expanded],
        ]);
    }
}
