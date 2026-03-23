<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('position_name');
            $table->string('category');
            $table->string('job_type')->index();
            $table->string('work_policy')->index();
            $table->string('experience_level')->index();
            $table->string('province')->index();
            $table->unsignedBigInteger('salary_min')->nullable();
            $table->unsignedBigInteger('salary_max')->nullable();
            $table->boolean('is_salary_hidden')->default(false);
            $table->text('requirements')->nullable();
            $table->longText('job_description');
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();

            $table->index(['updated_at', 'created_at']);
        });

        Schema::create('job_vacancy_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->cascadeOnDelete();
            $table->string('name')->index();
            $table->timestamps();
        });

        Schema::create('job_vacancy_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->cascadeOnDelete();
            $table->string('name')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancy_benefits');
        Schema::dropIfExists('job_vacancy_skills');
        Schema::dropIfExists('job_vacancies');
    }
};
