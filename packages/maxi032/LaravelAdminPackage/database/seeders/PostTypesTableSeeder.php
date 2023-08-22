<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \Maxi032\LaravelAdminPackage\Models\PostType;

class PostTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PostType::firstOrCreate([
            'type'   => 'downloads',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);

        PostType::firstOrCreate([
            'type'   => 'blog_articles',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);

        PostType::firstOrCreate([
            'type'   => 'products',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);

        PostType::firstOrCreate([
            'type'   => 'faq',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);
    }
}
