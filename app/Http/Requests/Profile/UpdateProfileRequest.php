<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return [];
        }

        $rules = [
            'user' => ['nullable', 'array:name'],
            'user.name' => ['sometimes', 'string', 'max:255'],
        ];

        if ($user->role === User::ROLE_SISWA) {
            return array_merge($rules, [
                'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
                'address' => ['sometimes', 'nullable', 'string'],
                'class_year' => ['sometimes', 'nullable', 'digits:4'],
                'profile' => ['sometimes', 'array:description,phone_number,address,class_year'],
                'profile.description' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'profile.phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
                'profile.address' => ['sometimes', 'nullable', 'string'],
                'profile.class_year' => ['sometimes', 'nullable', 'digits:4'],
            ]);
        }

        if ($user->role === User::ROLE_SEKOLAH) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:short_description,address,accreditation,expertise_fields'],
                'profile.short_description' => ['sometimes', 'string', 'max:1000'],
                'profile.address' => ['sometimes', 'string'],
                'profile.accreditation' => ['sometimes', 'string', Rule::in(['A', 'B', 'C', 'Belum Terakreditasi'])],
                'profile.expertise_fields' => ['sometimes', 'array', 'min:1'],
                'profile.expertise_fields.*' => ['required_with:profile.expertise_fields', 'string', 'max:100'],
            ]);
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:short_description,office_address,website_or_social_url,industry_sector,employee_total_range'],
                'profile.short_description' => ['sometimes', 'string', 'max:1000'],
                'profile.office_address' => ['sometimes', 'string'],
                'profile.website_or_social_url' => ['sometimes', 'nullable', 'url', 'max:255'],
                'profile.industry_sector' => ['sometimes', 'string', 'max:100'],
                'profile.employee_total_range' => ['sometimes', 'string', 'max:50'],
            ]);
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:short_description,business_address,business_type,owner_personal_nib'],
                'profile.short_description' => ['sometimes', 'string', 'max:1000'],
                'profile.business_address' => ['sometimes', 'string'],
                'profile.business_type' => ['sometimes', 'string', 'max:100'],
                'profile.owner_personal_nib' => ['sometimes', 'nullable', 'string', 'max:50'],
            ]);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:full_name'],
                'profile.full_name' => ['sometimes', 'string', 'max:255'],
            ]);
        }

        return $rules;
    }
}
