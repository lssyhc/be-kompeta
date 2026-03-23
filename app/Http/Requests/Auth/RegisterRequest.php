<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'role' => [
                'required',
                'string',
                Rule::in([User::ROLE_SEKOLAH, User::ROLE_MITRA]),
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ];

        if ($this->input('role') === User::ROLE_SEKOLAH) {
            return array_merge($rules, [
                'school_name' => ['required', 'string', 'max:255'],
                'npsn' => ['required', 'digits:8', 'unique:school_profiles,npsn'],
                'accreditation' => ['required', 'string', Rule::in(['A', 'B', 'C', 'Belum Terakreditasi'])],
                'address' => ['required', 'string'],
                'expertise_fields' => ['required', 'array', 'min:1'],
                'expertise_fields.*' => ['required', 'string', 'max:100'],
                'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_4' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_5' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'short_description' => ['required', 'string', 'max:1000'],
                'operational_license' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            ]);
        }

        if ($this->input('role') === User::ROLE_MITRA && $this->input('mitra_type') === User::MITRA_PERUSAHAAN) {
            return array_merge($rules, [
                'mitra_type' => ['required', Rule::in([User::MITRA_PERUSAHAAN, User::MITRA_UMKM])],
                'company_name' => ['required', 'string', 'max:255'],
                'nib' => ['required', 'digits:13', 'unique:company_profiles,nib'],
                'industry_sector' => ['required', 'string', 'max:100'],
                'employee_total_range' => ['required', 'string', 'max:50'],
                'office_address' => ['required', 'string'],
                'website_or_social_url' => ['nullable', 'url', 'max:255'],
                'short_description' => ['required', 'string', 'max:1000'],
                'company_logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_4' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_5' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'kemenkumham_decree' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            ]);
        }

        if ($this->input('role') === User::ROLE_MITRA && $this->input('mitra_type') === User::MITRA_UMKM) {
            return array_merge($rules, [
                'mitra_type' => ['required', Rule::in([User::MITRA_PERUSAHAAN, User::MITRA_UMKM])],
                'business_name' => ['required', 'string', 'max:255'],
                'owner_nik' => ['required', 'digits:16', 'unique:umkm_profiles,owner_nik'],
                'owner_personal_nib' => ['nullable', 'string', 'max:50'],
                'business_type' => ['required', 'string', 'max:100'],
                'business_address' => ['required', 'string'],
                'short_description' => ['required', 'string', 'max:1000'],
                'umkm_logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'owner_ktp_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_1' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_2' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_3' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_4' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_5' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        return array_merge($rules, [
            'mitra_type' => ['required_if:role,mitra', Rule::in([User::MITRA_PERUSAHAAN, User::MITRA_UMKM])],
        ]);
    }
}
