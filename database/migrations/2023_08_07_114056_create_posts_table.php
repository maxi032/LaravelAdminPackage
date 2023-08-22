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
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('type_id');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::table('posts', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('posts')->onUpdate('cascade')->onDelete('cascade');
            });

            Schema::table('posts', function (Blueprint $table) {
                $table->foreign('type_id')->references('id')->on('post_types')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::dropIfExists('posts');
        }
    }
};
