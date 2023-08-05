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
        if (!Schema::hasTable('category_types')) {
            Schema::create('category_types', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50)->nullable();
                $table->boolean('status')->default(false);
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
        if (Schema::hasTable('category_types')) {
            Schema::dropIfExists('category_types');
        }
    }
};
