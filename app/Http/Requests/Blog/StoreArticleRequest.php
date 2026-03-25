<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_type_id' => ['required', 'integer', 'exists:content_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'thumbnail' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
