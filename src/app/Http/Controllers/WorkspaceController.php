<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\FolderTreeService;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __construct(private FolderTreeService $treeService) {}

    public function index(): View
    {
        $tree = $this->treeService->build();
        $tags = Tag::orderBy('position')->orderBy('name')->get();

        return view('workspace.index', compact('tree', 'tags'));
    }
}
