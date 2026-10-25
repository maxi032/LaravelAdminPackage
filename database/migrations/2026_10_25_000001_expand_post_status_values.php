<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->smallInteger('status')->default(0)->change();
        });
    }

    public function down(): void
    {
        // Refuse to discard pending, draft or archived values during rollback.
        if (DB::table('posts')->whereNotIn('status', [0, 1])->exists()) {
            throw new RuntimeException('Convert pending, draft and archived posts to active or inactive before rolling back.');
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('status')->default(false)->change();
        });
    }
};
