<?php

namespace App\Http\Requests\Explore;

use App\Models\JobVacancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExploreIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $provinces = config('indonesia.provinces', []);

        return [
            'q' => ['nullable', 'string', 'max:100'],
            'job_types' => ['nullable', 'array'],
            'job_types.*' => ['string', Rule::in(array_keys(JobVacancy::jobTypeLabels()))],
            'work_policies' => ['nullable', 'array'],
            'work_policies.*' => ['string', Rule::in(array_keys(JobVacancy::workPolicyLabels()))],
            'provinces' => ['nullable', 'array'],
            'provinces.*' => ['string', Rule::in($provinces)],
            'experience_levels' => ['nullable', 'array'],
            'experience_levels.*' => ['string', Rule::in(array_keys(JobVacancy::experienceLabels()))],
            'updated_within' => ['nullable', 'string', Rule::in(['24h', '7d', '30d', 'any'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'job_types' => $this->normalizeArrayInput('job_types'),
            'work_policies' => $this->normalizeArrayInput('work_policies'),
            'provinces' => $this->normalizeArrayInput('provinces'),
            'experience_levels' => $this->normalizeArrayInput('experience_levels'),
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
