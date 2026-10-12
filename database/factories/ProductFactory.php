<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Maxi032\LaravelAdminPackage\Models\Post;

/** @extends Factory<Post> */
class ProductFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        // Supply the product type and category through for(..., 'type/category').
        return ['parent_id' => null, 'category_id' => null, 'status' => 1, 'sort_order' => 0];
    }

    public function electronics(): static
    {
        return $this->withTranslatedNames([
            ['Wireless headphones', 'Draadloze hoofdtelefoon'],
            ['Bluetooth speaker', 'Bluetooth-luidspreker'],
            ['Portable power bank', 'Draagbare powerbank'],
            ['USB-C docking station', 'USB-C-dockingstation'],
            ['Smart watch', 'Slim horloge'],
            ['Wireless keyboard', 'Draadloos toetsenbord'],
            ['Gaming mouse', 'Gamingmuis'],
            ['Digital camera', 'Digitale camera'],
            ['LED desk lamp', 'LED-bureaulamp'],
            ['Tablet stand', 'Tabletstandaard'],
        ]);
    }

    public function automotive(): static
    {
        return $this->withTranslatedNames([
            ['Car phone holder', 'Telefoonhouder voor de auto'],
            ['Tyre pressure gauge', 'Bandenspanningsmeter'],
            ['Portable tyre inflator', 'Draagbare bandenpomp'],
            ['Car vacuum cleaner', 'Autostofzuiger'],
            ['Dashboard camera', 'Dashboardcamera'],
            ['Battery charger', 'Acculader'],
            ['Car floor mats', 'Automatten'],
            ['Steering wheel cover', 'Stuurhoes'],
            ['Car cleaning kit', 'Autoreinigingsset'],
            ['Emergency roadside kit', 'Pechhulpkit'],
        ]);
    }

    private function withTranslatedNames(array $names): static
    {
        return $this->afterCreating(function (Post $post) use ($names) {
            [$english, $dutch] = $this->faker->randomElement($names);
            $model = strtoupper($this->faker->bothify('??-###'));
            foreach (['en' => $english, 'nl' => $dutch] as $language => $name) {
                $title = $name.' '.$model;
                $excerpt = $language === 'en'
                    ? $title.' combines reliable quality with practical everyday use.'
                    : $title.' combineert betrouwbare kwaliteit met praktisch dagelijks gebruik.';
                $details = $language === 'en'
                    ? 'Designed for convenience and ease of use. A practical addition to your collection.'
                    : 'Ontworpen voor gemak en gebruiksvriendelijkheid. Een praktische aanvulling op je collectie.';
                $post->translations()->create([
                    'language' => $language,
                    'title' => $title,
                    'slug' => Str::slug($title).'-'.$post->id,
                    'excerpt' => $excerpt,
                    'content' => '<p>'.$excerpt.'</p><p>'.$details.'</p>',
                ]);
            }
        });
    }
}
