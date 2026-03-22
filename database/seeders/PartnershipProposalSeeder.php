<?php

namespace Database\Seeders;

use App\Models\PartnershipProposal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PartnershipProposalSeeder extends Seeder
{
    public function run(): void
    {
        $schoolA = User::query()->where('email', 'school.a@kompeta.test')->firstOrFail();
        $schoolB = User::query()->where('email', 'school.b@kompeta.test')->firstOrFail();
        $companyA = User::query()->where('email', 'mitra.company.a@kompeta.test')->firstOrFail();
        $umkmA = User::query()->where('email', 'mitra.umkm.a@kompeta.test')->firstOrFail();

        $submittedProposalPdf = $this->storeLocalFile('partnership/proposals/proposal-sman2-ke-binajasa.pdf', 'Proposal kemitraan SMAN 2 ke Binajasa');
        $submittedSignature = $this->storeLocalFile('partnership/signatures/signature-sman2.pdf', 'Signature Kepala Sekolah SMAN 2');
        $draftProposalPdf = $this->storeLocalFile('partnership/proposals/proposal-roti-bunda-ke-sman1.pdf', 'Proposal kemitraan Roti Bunda ke SMAN 1');
        $draftSignature = $this->storeLocalFile('partnership/signatures/signature-roti-bunda.pdf', 'Signature Owner Roti Bunda');

        PartnershipProposal::query()->updateOrCreate(
            [
                'proposer_user_id' => $schoolB->id,
                'target_user_id' => $companyA->id,
            ],
            [
                'school_user_id' => $schoolB->id,
                'mitra_user_id' => $companyA->id,
                'proposal_pdf_path' => $submittedProposalPdf,
                'signature_path' => $submittedSignature,
                'notes' => 'Pengajuan kerja sama magang siswa kelas XII untuk periode Juli-Desember.',
                'status' => PartnershipProposal::STATUS_SUBMITTED,
                'submitted_at' => now()->subDays(9),
            ]
        );

        PartnershipProposal::query()->updateOrCreate(
            [
                'proposer_user_id' => $umkmA->id,
                'target_user_id' => $schoolA->id,
            ],
            [
                'school_user_id' => $schoolA->id,
                'mitra_user_id' => $umkmA->id,
                'proposal_pdf_path' => $draftProposalPdf,
                'signature_path' => $draftSignature,
                'notes' => 'Draft kolaborasi kewirausahaan untuk program kelas industri UMKM.',
                'status' => PartnershipProposal::STATUS_DRAFT,
                'submitted_at' => null,
            ]
        );
    }

    private function storeLocalFile(string $path, string $content): string
    {
        Storage::disk('local')->put($path, $content);

        return $path;
    }
}
