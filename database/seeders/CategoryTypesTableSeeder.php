<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \Maxi032\LaravelAdminPackage\Models\CategoryType;

class CategoryTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds for CategoryType.
     */
    public function run(): void
    {
        CategoryType::firstOrCreate([
            'type'   => 'downloads',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);

        CategoryType::firstOrCreate([
            'type'   => 'blog_articles',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);

        CategoryType::firstOrCreate([
            'type'   => 'products',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);

        CategoryType::firstOrCreate([
            'type'   => 'faq',
            'status' => false,
        ], [
            'created_at' => Carbon::now()
        ]);
    }
}
