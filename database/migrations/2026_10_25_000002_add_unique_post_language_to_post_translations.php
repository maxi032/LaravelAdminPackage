<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Include soft-deleted rows because they can be restored.
        $duplicate = DB::table('post_translations')
            ->select('post_id', 'language')
            ->groupBy('post_id', 'language')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate !== null) {
            throw new RuntimeException(
                'Resolve duplicate post_translations for post_id '.$duplicate->post_id.
                ' and language '.$duplicate->language.' (including soft-deleted rows) before adding the unique constraint.'
            );
        }

        Schema::table('post_translations', function (Blueprint $table) {
            $table->unique(['post_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'language']);
        });
    }
};
