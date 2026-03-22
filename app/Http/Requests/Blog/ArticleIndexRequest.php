<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'content_type_id' => ['nullable', 'integer', 'exists:content_types,id'],
            'content_type_slug' => ['nullable', 'string', 'exists:content_types,slug'],
            'sort' => ['nullable', 'string', Rule::in(['latest', 'most_read'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => is_string($this->input('q')) ? trim((string) $this->input('q')) : $this->input('q'),
        ]);
    }
}
