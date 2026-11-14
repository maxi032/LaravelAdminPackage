<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_types', function (Blueprint $table) {
            $table->string('icon', 50)->default('cil-description');
        });

        foreach ([
            'blog_articles' => 'cil-newspaper',
            'downloads' => 'cil-cloud-download',
            'faq' => 'cil-speech',
            'products' => 'cil-basket',
        ] as $type => $icon) {
            DB::table('post_types')->where('type', $type)->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        Schema::table('post_types', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
