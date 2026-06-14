<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNoteRequest extends FormRequest
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
        $note = $this->route('note');
        $folderId = $this->input('folder_id', $note?->folder_id);

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('notes', 'name')->where(function ($query) use ($folderId) {
                    $query->whereNull('deleted_at');

                    return $folderId === null
                        ? $query->whereNull('folder_id')
                        : $query->where('folder_id', $folderId);
                })->ignore($note),
            ],
            'content' => ['sometimes', 'nullable', 'string'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Já existe uma nota com esse nome nesta pasta.',
        ];
    }
}
