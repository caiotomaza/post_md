<?php

namespace App\Http\Requests;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        if (is_string($name) && $name !== '' && !str_ends_with($name, '.md')) {
            $this->merge(['name' => $name . '.md']);
        }
    }

    public function rules(): array
    {
        $folderId = $this->input('folder_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('notes', 'name')->where(function ($query) use ($folderId) {
                    $query->whereNull('deleted_at');

                    return $folderId === null
                        ? $query->whereNull('folder_id')
                        : $query->where('folder_id', $folderId);
                })->ignore($this->route('note')),
            ],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
            'content' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Já existe uma nota com esse nome nesta pasta.',
        ];
    }
}
