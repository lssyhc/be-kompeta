<?php

namespace App\Http\Resources\School;

use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class SchoolCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->schoolProfile;

        if (! $profile instanceof SchoolProfile) {
            return [
                'id' => $this->id,
                'school_name' => $this->name,
                'address' => null,
                'logo_url' => null,
                'expertise_fields' => [],
            ];
        }

        $expertiseFields = $profile->getAttribute('expertise_fields');

        return [
            'id' => $this->id,
            'school_name' => $profile->school_name,
            'address' => $profile->address,
            'logo_url' => $profile->logo_url,
            'expertise_fields' => is_array($expertiseFields)
                ? $expertiseFields
                : (json_decode((string) $expertiseFields, true) ?? []),
        ];
    }
}
