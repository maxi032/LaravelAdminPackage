<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_option', function (Blueprint $table) {
            $table->boolean('visible_in_post')->default(false);
        });

        // Preserve visibility for definitions that already appeared in the post editor.
        DB::table('post_option')->update(['visible_in_post' => true]);
    }

    public function down(): void
    {
        Schema::table('post_option', function (Blueprint $table) {
            $table->dropColumn('visible_in_post');
        });
    }
};
