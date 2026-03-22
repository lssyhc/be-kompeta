<?php

namespace App\Http\Requests\Partnership;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnershipProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'proposal_pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'signature_file' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
