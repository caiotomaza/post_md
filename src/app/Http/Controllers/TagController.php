<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Note;
use App\Models\Tag;
use App\Services\FolderTreeService;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function __construct(private FolderTreeService $treeService) {}

    public function index(): JsonResponse
    {
        $tags = Tag::orderBy('position')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $tags->map(fn ($t) => $this->treeService->formatTag($t))->values(),
        ]);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $hex = $request->input('color_hex');
        if ($hex) {
            $hex = Tag::normalizeHex($hex);
        }

        $tag = Tag::create([
            'name' => $request->input('name'),
            'display_mode' => $request->input('display_mode'),
            'color_hex' => $hex,
            'emoji' => $request->input('emoji'),
            'position' => $request->input('position', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag criada.',
            'data' => $this->treeService->formatTag($tag),
        ], 201);
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['color_hex']) && $data['color_hex']) {
            $data['color_hex'] = Tag::normalizeHex($data['color_hex']);
        }

        $tag->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tag atualizada.',
            'data' => $this->treeService->formatTag($tag),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag excluída.',
        ]);
    }

    public function attach(Note $note, Tag $tag): JsonResponse
    {
        if (!$note->tags()->where('tag_id', $tag->id)->exists()) {
            $maxPosition = $note->tags()->max('note_tag.position') ?? -1;
            $note->tags()->attach($tag->id, ['position' => $maxPosition + 1]);
        }

        $note->load('tags');

        return response()->json([
            'success' => true,
            'message' => 'Tag associada.',
            'data' => $note->tags->map(fn ($t) => $this->treeService->formatTag($t))->values(),
        ]);
    }

    public function detach(Note $note, Tag $tag): JsonResponse
    {
        $note->tags()->detach($tag->id);

        $note->load('tags');

        return response()->json([
            'success' => true,
            'message' => 'Tag removida.',
            'data' => $note->tags->map(fn ($t) => $this->treeService->formatTag($t))->values(),
        ]);
    }
}
