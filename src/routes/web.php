<?php

use App\Http\Controllers\FolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkspaceController::class, 'index']);

// Folders
Route::get('/folders/tree', [FolderController::class, 'tree']);
Route::post('/folders', [FolderController::class, 'store']);
Route::patch('/folders/{folder}', [FolderController::class, 'update']);
Route::delete('/folders/{folder}', [FolderController::class, 'destroy']);
Route::post('/folders/{folder}/move', [FolderController::class, 'move']);
Route::post('/folders/{folder}/toggle', [FolderController::class, 'toggle']);

// Notes
Route::post('/notes', [NoteController::class, 'store']);
Route::get('/notes/{note}', [NoteController::class, 'show']);
Route::patch('/notes/{note}', [NoteController::class, 'update']);
Route::delete('/notes/{note}', [NoteController::class, 'destroy']);
Route::post('/notes/{note}/move', [NoteController::class, 'move']);

// Tags
Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);
Route::patch('/tags/{tag}', [TagController::class, 'update']);
Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
Route::post('/notes/{note}/tags/{tag}', [TagController::class, 'attach']);
Route::delete('/notes/{note}/tags/{tag}', [TagController::class, 'detach']);
