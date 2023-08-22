<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Models\Post;

class PostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sortOrder=0;
        Post::firstOrCreate([
            'parent_id' => null,
            'type_id'   => PostType::where('type', 'downloads')->first()->id,
            'sort_order'   => $sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        Post::firstOrCreate([
            'parent_id' => null,
            'type_id'   => PostType::where('type', 'blog_articles')->first()->id,
            'sort_order' => ++$sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        Post::firstOrCreate([
            'parent_id' => null,
            'type_id'   => PostType::where('type', 'faq')->first()->id,
            'sort_order' => ++$sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        Post::firstOrCreate([
            'parent_id' => null,
            'type_id'   => PostType::where('type', 'products')->first()->id,
            'sort_order' => ++$sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

    }
}
