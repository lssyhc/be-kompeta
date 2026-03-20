<?php

namespace App\Http\Requests\ForYou;

use App\Models\JobVacancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ForYouIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $provinces = config('indonesia.provinces', []);

        return [
            'sort_by' => ['nullable', 'string', Rule::in(['paling_relevan', 'baru_ditambahkan', 'remote_wfh', 'hybrid', 'part_time'])],
            'preferred_skills' => ['nullable', 'array'],
            'preferred_skills.*' => ['string', 'max:100'],
            'preferred_work_policies' => ['nullable', 'array'],
            'preferred_work_policies.*' => ['string', Rule::in(array_keys(JobVacancy::workPolicyLabels()))],
            'preferred_job_types' => ['nullable', 'array'],
            'preferred_job_types.*' => ['string', Rule::in(array_keys(JobVacancy::jobTypeLabels()))],
            'preferred_province' => ['nullable', 'string', Rule::in($provinces)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'preferred_skills' => $this->normalizeArrayInput('preferred_skills'),
            'preferred_work_policies' => $this->normalizeArrayInput('preferred_work_policies'),
            'preferred_job_types' => $this->normalizeArrayInput('preferred_job_types'),
        ]);
    }

    private function normalizeArrayInput(string $key): mixed
    {
        $value = $this->input($key);

        if (is_string($value)) {
            $items = array_filter(array_map('trim', explode(',', $value)), fn ($item) => $item !== '');

            return array_values(array_unique($items));
        }

        if (is_array($value)) {
            return array_values(array_unique(array_filter($value, fn ($item) => is_string($item) && trim($item) !== '')));
        }

        return $value;
    }
}
