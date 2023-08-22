<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoryTypesTableSeeder::class,
            CategoryTableSeeder::class,
            PostTypesTableSeeder::class,
            PostTableSeeder::class,
            PostTranslationsTableSeeder::class,
        ]);
    }
}
