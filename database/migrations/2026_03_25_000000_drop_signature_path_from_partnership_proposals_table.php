<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partnership_proposals', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('partnership_proposals', function (Blueprint $table) {
            $table->string('signature_path')->after('proposal_pdf_path');
        });
    }
};
