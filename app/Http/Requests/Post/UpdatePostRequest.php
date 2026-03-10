<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id'                       => ['sometimes', 'exists:categories,id'],
            'status'                            => ['sometimes', 'in:draft,published'],
            'featured_image'                    => ['nullable', 'string'],
            'translations'                      => ['sometimes', 'array'],
            'translations.en.title'             => ['sometimes', 'string', 'max:255'],
            'translations.en.content'           => ['sometimes', 'string'],
            'translations.en.excerpt'           => ['nullable', 'string', 'max:500'],
            'translations.bn.title'             => ['nullable', 'string', 'max:255'],
            'translations.bn.content'           => ['nullable', 'string'],
            'translations.bn.excerpt'           => ['nullable', 'string', 'max:500'],
            'translations.es.title'             => ['nullable', 'string', 'max:255'],
            'translations.es.content'           => ['nullable', 'string'],
            'translations.es.excerpt'           => ['nullable', 'string', 'max:500'],
            'translations.*.meta_title'         => ['nullable', 'string', 'max:60'],
            'translations.*.meta_description'   => ['nullable', 'string', 'max:160'],
        ];
    }
}
