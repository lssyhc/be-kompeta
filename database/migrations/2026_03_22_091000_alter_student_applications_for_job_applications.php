<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_applications', function (Blueprint $table) {
            $table->foreignId('job_vacancy_id')->nullable()->after('student_profile_id')->constrained('job_vacancies')->nullOnDelete();
            $table->foreignId('mitra_user_id')->nullable()->after('job_vacancy_id')->constrained('users')->nullOnDelete();
            $table->string('cv_path')->nullable()->after('submit_status');
            $table->text('cover_letter')->nullable()->after('cv_path');
            $table->string('status', 30)->default('applied')->after('cover_letter')->index();
            $table->timestamp('applied_at')->nullable()->after('status')->index();

            $table->unique(['student_profile_id', 'job_vacancy_id'], 'student_applications_student_job_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_applications', function (Blueprint $table) {
            $table->dropUnique('student_applications_student_job_unique');
            $table->dropColumn(['applied_at', 'status', 'cover_letter', 'cv_path']);
            $table->dropConstrainedForeignId('mitra_user_id');
            $table->dropConstrainedForeignId('job_vacancy_id');
        });
    }
};
