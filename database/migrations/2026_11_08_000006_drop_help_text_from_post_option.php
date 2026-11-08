<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_option', function (Blueprint $table) {
            $table->dropColumn('help_text');
        });
    }

    public function down(): void
    {
        Schema::table('post_option', function (Blueprint $table) {
            $table->string('help_text', 500)->nullable();
        });
    }
};
