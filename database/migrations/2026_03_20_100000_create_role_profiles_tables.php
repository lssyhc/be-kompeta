<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('school_name');
            $table->string('npsn', 8)->unique();
            $table->string('accreditation');
            $table->text('address');
            $table->json('expertise_fields');
            $table->string('logo_path');
            $table->string('image_1_path')->nullable();
            $table->string('image_2_path')->nullable();
            $table->string('image_3_path')->nullable();
            $table->string('image_4_path')->nullable();
            $table->string('image_5_path')->nullable();
            $table->text('short_description');
            $table->string('operational_license_path');
            $table->timestamps();
        });

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('nib', 13)->unique();
            $table->string('industry_sector');
            $table->string('employee_total_range');
            $table->text('office_address');
            $table->string('website_or_social_url')->nullable();
            $table->text('short_description');
            $table->string('company_logo_path');
            $table->string('image_1_path')->nullable();
            $table->string('image_2_path')->nullable();
            $table->string('image_3_path')->nullable();
            $table->string('image_4_path')->nullable();
            $table->string('image_5_path')->nullable();
            $table->string('kemenkumham_decree_path');
            $table->timestamps();
        });

        Schema::create('umkm_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('owner_nik', 16)->unique();
            $table->string('owner_personal_nib')->nullable();
            $table->string('business_type');
            $table->text('business_address');
            $table->string('umkm_logo_path');
            $table->string('owner_ktp_photo_path');
            $table->text('short_description');
            $table->string('image_1_path');
            $table->string('image_2_path');
            $table->string('image_3_path');
            $table->string('image_4_path');
            $table->string('image_5_path');
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('school_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('nisn', 10)->unique();
            $table->string('photo_profile_path')->nullable();
            $table->string('major');
            $table->string('school_origin');
            $table->string('graduation_status');
            $table->string('unique_code', 16)->unique();
            $table->text('description')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('student_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('student_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('position');
            $table->string('company_name');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->date('achievement_date');
            $table->string('institution_name');
            $table->timestamps();
        });

        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('role_type');
            $table->date('submitted_at');
            $table->string('submit_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_applications');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('student_experiences');
        Schema::dropIfExists('student_skills');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('umkm_profiles');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('school_profiles');
    }
};
