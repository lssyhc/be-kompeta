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
                'profile' => ['sometimes', 'array:full_name,description,phone_number,address'],
                'profile.full_name' => ['sometimes', 'string', 'max:255'],
                'profile.description' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'profile.phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
                'profile.address' => ['sometimes', 'nullable', 'string'],
                'photo_profile' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        if ($user->role === User::ROLE_SEKOLAH) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:accreditation,address,expertise_fields,short_description,socials'],
                'profile.accreditation' => ['sometimes', 'string', Rule::in(['A', 'B', 'C', 'Belum Terakreditasi'])],
                'profile.address' => ['sometimes', 'string'],
                'profile.expertise_fields' => ['sometimes', 'array', 'min:1'],
                'profile.expertise_fields.*' => ['required_with:profile.expertise_fields', 'string', 'max:100'],
                'profile.short_description' => ['sometimes', 'string', 'max:1000'],
                'profile.socials' => ['sometimes', 'nullable', 'array'],
                'profile.socials.website' => ['nullable', 'url', 'max:255'],
                'profile.socials.instagram' => ['nullable', 'url', 'max:255'],
                'profile.socials.linkedin' => ['nullable', 'url', 'max:255'],
                'profile.socials.whatsapp' => ['nullable', 'string', 'max:30'],
                'logo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_1' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_2' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_3' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_4' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_5' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'operational_license' => ['sometimes', 'file', 'mimes:pdf', 'max:5120'],
            ]);
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:industry_sector,employee_total_range,office_address,socials,short_description'],
                'profile.industry_sector' => ['sometimes', 'string', 'max:100'],
                'profile.employee_total_range' => ['sometimes', 'string', 'max:50'],
                'profile.office_address' => ['sometimes', 'string'],
                'profile.socials' => ['sometimes', 'nullable', 'array'],
                'profile.socials.website' => ['nullable', 'url', 'max:255'],
                'profile.socials.instagram' => ['nullable', 'url', 'max:255'],
                'profile.socials.linkedin' => ['nullable', 'url', 'max:255'],
                'profile.socials.whatsapp' => ['nullable', 'string', 'max:30'],
                'profile.short_description' => ['sometimes', 'string', 'max:1000'],
                'company_logo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_1' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_2' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_3' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_4' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_5' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'kemenkumham_decree' => ['sometimes', 'file', 'mimes:pdf', 'max:5120'],
            ]);
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:owner_personal_nib,business_type,business_address,socials,short_description'],
                'profile.owner_personal_nib' => ['sometimes', 'nullable', 'string', 'max:50'],
                'profile.business_type' => ['sometimes', 'string', 'max:100'],
                'profile.business_address' => ['sometimes', 'string'],
                'profile.socials' => ['sometimes', 'nullable', 'array'],
                'profile.socials.website' => ['nullable', 'url', 'max:255'],
                'profile.socials.instagram' => ['nullable', 'url', 'max:255'],
                'profile.socials.linkedin' => ['nullable', 'url', 'max:255'],
                'profile.socials.whatsapp' => ['nullable', 'string', 'max:30'],
                'profile.short_description' => ['sometimes', 'string', 'max:1000'],
                'umkm_logo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'owner_ktp_photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_1' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_2' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_3' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_4' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'image_5' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return array_merge($rules, [
                'profile' => ['sometimes', 'array:full_name'],
                'profile.full_name' => ['sometimes', 'string', 'max:255'],
                'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        return $rules;
    }
}
