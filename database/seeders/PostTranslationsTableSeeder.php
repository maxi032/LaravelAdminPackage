<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Maxi032\LaravelAdminPackage\Models\Post;
use Faker\Factory as FakerFactory;

class PostTranslationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = collect(config('laravel-admin-package.allowed_languages'))->pluck('code');

        // Change this to the number of fake translations you want for each Post
        $numberOfTranslationsPerPost = 3;

            foreach ($languages as $language) {
                $faker = ($language !== 'en') ? FakerFactory::create($language . '_' . strtoupper($language)) : FakerFactory::create($language.'_US');
                for ($i = 0; $i < $numberOfTranslationsPerPost; $i++) {
                    // Loop through each Post and create multiple translations for each language
                    Post::with('type')->each(function (Post $post) use ($languages, $numberOfTranslationsPerPost, $i, $faker, $language) {
                        $title = $post->type->type . '-' . $i . '-' . $faker->realTextBetween(20, 90);
                        $post->translations()->create([
                            'language' => $language,
                            'title'    => $title,
                            'slug'     => Str::slug($title),
                            'excerpt'  => $post->type->type . '-excerpt-' . $i . '-' . $faker->realTextBetween(50, 150),
                            'content'  => $faker->realText(550),
                            'post_id'  => $post->id,
                        ]);

                    });

                }
            }

    }
}
