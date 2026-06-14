<?php

namespace App\Http\Requests;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function rules(): array
    {
        $tag = $this->route('tag');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('tags', 'name')->ignore($tag)],
            'display_mode' => ['sometimes', 'required', Rule::in(['color', 'emoji', 'both'])],
            'color_hex' => ['nullable', 'string'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $tag = $this->route('tag');
            $mode = $this->input('display_mode', $tag?->display_mode);
            $hex = $this->input('color_hex', $tag?->color_hex);
            $emoji = $this->input('emoji', $tag?->emoji);

            if (in_array($mode, ['color', 'both']) && empty($hex)) {
                $v->errors()->add('color_hex', 'A cor é obrigatória para este modo.');
            }
            if (!empty($hex) && !Tag::isValidHex($hex)) {
                $v->errors()->add('color_hex', 'Cor inválida. Use #RGB ou #RRGGBB.');
            }
            if (in_array($mode, ['emoji', 'both']) && empty($emoji)) {
                $v->errors()->add('emoji', 'O emoji é obrigatório para este modo.');
            }
        });
    }
}
