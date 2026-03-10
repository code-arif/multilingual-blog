<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'status' => 'in:draft,published',
            'featured_image' => 'nullable|string|max:255',
            'translations' => 'required|array|min:1',
            'translations.en' => 'required|array',
            'translations.en.title' => 'required|string|max:255|min:3',
            'translations.en.content' => 'required|string|min:10',
            'translations.en.excerpt' => 'nullable|string|max:500',
            'translations.en.meta_title' => 'nullable|string|max:60',
            'translations.en.meta_description' => 'nullable|string|max:160',
            'translations.bn' => 'nullable|array',
            'translations.bn.title' => 'required_with:translations.bn|string|max:255',
            'translations.bn.content' => 'required_with:translations.bn|string',
            'translations.es' => 'nullable|array',
            'translations.es.title' => 'required_with:translations.es|string|max:255',
            'translations.es.content' => 'required_with:translations.es|string',
        ];
    }
}
