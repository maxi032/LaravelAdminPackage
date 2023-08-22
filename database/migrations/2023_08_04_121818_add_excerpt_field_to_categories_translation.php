<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add excerpt column to category_translations
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('category_translations', 'excerpt')) {
            Schema::table('category_translations', function ($table) {
                $table->text('excerpt')->after('title')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('category_translations', 'excerpt')) {
            Schema::table('category_translations', function ($table) {
                $table->dropColumn('excerpt');
            });
        }
    }
};
