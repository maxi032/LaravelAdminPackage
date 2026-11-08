<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some installations already renamed the column manually.
        if (Schema::hasColumn('post_option', 'is_eagerloaded')) {
            Schema::table('post_option', function (Blueprint $table) {
                $table->renameColumn('is_eagerloaded', 'is_eager_loaded');
            });
        }
    }

    public function down(): void
    {
        Schema::table('post_option', function (Blueprint $table) {
            $table->renameColumn('is_eager_loaded', 'is_eagerloaded');
        });
    }
};
