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
        if (!Schema::hasTable('category_translations')) {
            Schema::create('category_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade')->onDelete('cascade');
                $table->string('title', '150');
                $table->string('slug', '150');
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
        if (Schema::hasTable('category_translations')) {
            Schema::dropIfExists('category_translations');
        }
    }
};
