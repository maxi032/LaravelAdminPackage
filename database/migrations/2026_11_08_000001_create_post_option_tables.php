<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_option', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label', 150);
            $table->string('help_text', 500)->nullable();
            $table->string('type', 16);
            $table->boolean('is_translatable')->default(false);
            // Keep the legacy column for installations that already ran this migration.
            $table->boolean('is_builtin')->default(false);
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('post_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_option_id')->constrained('post_option')->restrictOnDelete();
            // The empty string is the locale for shared values.
            $table->string('locale', 64)->default('');
            $table->boolean('boolean_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
            $table->unique(['post_id', 'post_option_id', 'locale'], 'post_option_values_post_option_locale_unique');
            $table->index(['post_option_id', 'locale', 'boolean_value', 'post_id'], 'post_option_values_boolean_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_option_values');
        Schema::dropIfExists('post_option');
    }
};
