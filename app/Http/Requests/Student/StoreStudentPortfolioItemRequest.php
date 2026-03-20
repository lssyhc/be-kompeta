<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentPortfolioItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:skill,experience,achievement,application'],
            'title' => ['required_if:type,skill,experience,achievement', 'nullable', 'string', 'max:255'],
            'description' => ['required_if:type,experience,achievement', 'nullable', 'string'],
            'position' => ['required_if:type,experience', 'nullable', 'string', 'max:255'],
            'company_name' => ['required_if:type,experience,application', 'nullable', 'string', 'max:255'],
            'start_date' => ['required_if:type,experience', 'nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'achievement_date' => ['required_if:type,achievement', 'nullable', 'date'],
            'institution_name' => ['required_if:type,achievement', 'nullable', 'string', 'max:255'],
            'role_type' => ['required_if:type,application', 'nullable', 'string', 'max:255'],
            'submitted_at' => ['required_if:type,application', 'nullable', 'date'],
            'submit_status' => ['required_if:type,application', 'nullable', 'string', 'max:100'],
        ];
    }
}
