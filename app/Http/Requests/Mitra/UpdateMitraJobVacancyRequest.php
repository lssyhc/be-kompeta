<?php

namespace App\Http\Requests\Mitra;

use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMitraJobVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->role === User::ROLE_MITRA;
    }

    public function rules(): array
    {
        $provinces = config('indonesia.provinces', []);

        return [
            'position_name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'job_type' => ['sometimes', 'string', Rule::in(array_keys(JobVacancy::jobTypeLabels()))],
            'work_policy' => ['sometimes', 'string', Rule::in(array_keys(JobVacancy::workPolicyLabels()))],
            'experience_level' => ['sometimes', 'string', Rule::in(array_keys(JobVacancy::experienceLabels()))],
            'province' => ['sometimes', 'string', Rule::in($provinces)],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'is_salary_hidden' => ['sometimes', 'boolean'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'job_description' => ['sometimes', 'string', 'max:10000'],
            'is_published' => ['sometimes', 'boolean'],
            'skills' => ['nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:100'],
            'benefits' => ['nullable', 'array', 'max:20'],
            'benefits.*' => ['string', 'max:100'],
        ];
    }
}
