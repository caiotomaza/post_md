<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveNoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_folder_id' => ['nullable', 'integer', 'exists:folders,id'],
        ];
    }
}
