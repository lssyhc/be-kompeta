<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
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
        $rules = [
            'role' => ['required', 'string', Rule::in([
                User::ROLE_ADMIN,
                User::ROLE_SEKOLAH,
                User::ROLE_MITRA,
                User::ROLE_SISWA,
            ])],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];

        if ($this->input('role') === User::ROLE_SISWA) {
            return array_merge($rules, [
                'nisn' => ['required', 'digits:10'],
                'school_origin' => ['required', 'string', 'max:255'],
                'unique_code' => ['required', 'string', 'max:16'],
            ]);
        }

        return array_merge($rules, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
    }
}
