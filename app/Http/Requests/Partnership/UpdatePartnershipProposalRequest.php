<?php

namespace App\Http\Requests\Partnership;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnershipProposalRequest extends FormRequest
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
            'proposal_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'signature_file' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
