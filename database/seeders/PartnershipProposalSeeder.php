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
        $draftProposalPdf = $this->storeLocalFile('partnership/proposals/proposal-roti-bunda-ke-sman1.pdf', 'Proposal kemitraan Roti Bunda ke SMAN 1');

        PartnershipProposal::query()->updateOrCreate(
            [
                'proposer_user_id' => $schoolB->id,
                'target_user_id' => $companyA->id,
            ],
            [
                'school_user_id' => $schoolB->id,
                'mitra_user_id' => $companyA->id,
                'proposal_pdf_path' => $submittedProposalPdf,
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
                'notes' => 'Kolaborasi kewirausahaan untuk program kelas industri UMKM.',
                'status' => PartnershipProposal::STATUS_SUBMITTED,
                'submitted_at' => now()->subDays(3),
            ]
        );
    }

    private function storeLocalFile(string $path, string $label): string
    {
        $binary = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF";

        Storage::disk('local')->put($path, $binary);

        return $path;
    }
}
