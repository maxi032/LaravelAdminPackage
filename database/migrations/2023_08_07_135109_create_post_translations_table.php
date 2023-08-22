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
        if (!Schema::hasTable('post_translations')) {
            Schema::create('post_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id');
                $table->foreign('post_id')->references('id')->on('posts')->onUpdate('cascade')->onDelete('cascade');
                $table->string('title', '150');
                $table->string('slug', '150');
                $table->text('excerpt')->nullable();
                $table->longText('content');
                $table->string('language', '2');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('post_translations')) {
            Schema::dropIfExists('post_translations');
        }
    }
};
