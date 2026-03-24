<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->json('socials')->nullable()->after('office_address');
            $table->dropColumn('website_or_social_url');
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->json('socials')->nullable()->after('business_address');
        });

        Schema::table('school_profiles', function (Blueprint $table) {
            $table->json('socials')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('website_or_social_url')->nullable()->after('office_address');
            $table->dropColumn('socials');
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->dropColumn('socials');
        });

        Schema::table('school_profiles', function (Blueprint $table) {
            $table->dropColumn('socials');
        });
    }
};
