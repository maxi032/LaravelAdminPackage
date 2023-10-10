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
        if (!Schema::hasColumn('posts', 'category_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->after('type_id')->nullable();
                $table->foreign('category_id')->references('id')->on('categories')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('posts', 'category_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }
    }
};
