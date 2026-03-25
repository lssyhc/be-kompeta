<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'digits:10', 'unique:student_profiles,nisn'],
            'photo_profile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'major' => ['required', 'string', 'max:100'],
            'school_origin' => ['required', 'string', 'max:255'],
            'graduation_status' => ['required', 'string', 'max:100'],
            'class_year' => ['required', 'digits:4'],
            'socials' => ['nullable', 'array'],
            'socials.website' => ['nullable', 'url', 'max:255'],
            'socials.instagram' => ['nullable', 'url', 'max:255'],
            'socials.linkedin' => ['nullable', 'url', 'max:255'],
            'socials.whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];
    }
}
