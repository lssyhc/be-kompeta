<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mitra_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('proposal_pdf_path');
            $table->string('signature_path');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('submitted')->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();

            $table->index(['school_user_id', 'mitra_user_id']);
            $table->index(['proposer_user_id', 'target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_proposals');
    }
};
