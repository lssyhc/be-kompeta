<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
            'full_name' => ['sometimes', 'string', 'max:255'],
            'nisn' => ['sometimes', 'digits:10', Rule::unique('student_profiles', 'nisn')->ignore($this->route('id'))],
            'photo_profile' => ['nullable', 'image', 'max:2048'],
            'major' => ['sometimes', 'string', 'max:100'],
            'school_origin' => ['sometimes', 'string', 'max:255'],
            'graduation_status' => ['sometimes', 'string', 'max:100'],
            'class_year' => ['nullable', 'digits:4'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];
    }
}
