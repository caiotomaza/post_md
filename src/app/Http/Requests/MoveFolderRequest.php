<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveFolderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_parent_id' => ['nullable', 'integer', 'exists:folders,id'],
        ];
    }
}
