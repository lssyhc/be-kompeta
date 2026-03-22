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
        if (! Schema::hasColumn('articles', 'excerpt')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('excerpt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('articles', 'excerpt')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('slug');
        });
    }
};
