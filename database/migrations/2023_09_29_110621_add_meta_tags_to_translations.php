<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('post_translations', 'meta_title') && !Schema::hasColumn('post_translations', 'meta_description') && !Schema::hasColumn('post_translations', 'meta_keywords')) {
            Schema::table('post_translations', function (Blueprint $table) {
                $table->string('meta_title', 150)->nullable()->after('content');
                $table->string('meta_keywords')->nullable()->after('content');
                $table->string('meta_description')->nullable()->after('content');
            });
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('post_translations', 'meta_title') && Schema::hasColumn('post_translations', 'meta_description') && Schema::hasColumn('post_translations', 'meta_keywords')) {
            Schema::table('post_translations', function (Blueprint $table) {
                $table->dropColumn('meta_title');
                $table->dropColumn('meta_keywords');
                $table->dropColumn('meta_description');
            });
        };
    }
};
