<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:4096'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
