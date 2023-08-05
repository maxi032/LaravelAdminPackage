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
        if (!Schema::hasColumn('category_translations', 'excerpt')) {
            Schema::table('category_translations', function ($table) {
                $table->string('excerpt', 255)->after('title')->nullable();
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
