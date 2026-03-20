<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'digits:10', 'unique:student_profiles,nisn'],
            'photo_profile' => ['nullable', 'image', 'max:2048'],
            'major' => ['required', 'string', 'max:100'],
            'school_origin' => ['required', 'string', 'max:255'],
            'graduation_status' => ['required', 'string', 'max:100'],
            'class_year' => ['nullable', 'digits:4'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];
    }
}
