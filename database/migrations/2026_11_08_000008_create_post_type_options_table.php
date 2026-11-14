<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_type_options', function (Blueprint $table) {
            $table->foreignId('post_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_option_id')->constrained('post_option')->cascadeOnDelete();
            $table->unique(['post_type_id', 'post_option_id']);
            $table->index('post_option_id');
        });

        // Include trashed rows so restored definitions and types keep their previous availability.
        DB::table('post_type_options')->insertUsing(
            ['post_type_id', 'post_option_id'],
            DB::table('post_types')->crossJoin('post_option')->select('post_types.id', 'post_option.id'),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('post_type_options');
    }
};
