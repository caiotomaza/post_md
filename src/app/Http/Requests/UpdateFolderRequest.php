<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'is_expanded' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
