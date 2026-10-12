<?php

namespace Database\Seeders;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // PostType's legacy fillable list omits 'type'; set it explicitly for new rows.
            $postType = PostType::firstOrNew(['type' => 'products']);
            if (!$postType->exists) {
                $postType->forceFill(['type' => 'products', 'status' => true])->save();
            }
            $postType = PostType::whereKey($postType->id)->lockForUpdate()->firstOrFail();
            $categoryType = CategoryType::firstOrCreate(['type' => 'products'], ['status' => true]);
            $position = (int) Post::where('type_id', $postType->id)->max('sort_order');

            foreach ([
                'electronics' => ['en' => 'Electronics', 'nl' => 'Elektronica'],
                'automotive' => ['en' => 'Automotive', 'nl' => 'Auto-industrie'],
            ] as $family => $titles) {
                $category = Category::where('type_id', $categoryType->id)
                    ->whereHas('translations', fn ($query) => $query->where('language', 'en')->where('title', $titles['en']))
                    ->first();
                if (!$category) {
                    $category = Category::create([
                        'type_id' => $categoryType->id,
                        'status' => true,
                        'sort_order' => (int) Category::where('type_id', $categoryType->id)->max('sort_order') + 1,
                    ]);
                }
                foreach ($titles as $language => $title) {
                    $category->translations()->firstOrCreate(['language' => $language], [
                        'title' => $title,
                        'slug' => Str::slug($title),
                        'content' => $language === 'en'
                            ? 'Explore our '.$title.' products.'
                            : 'Ontdek onze producten in de categorie '.$title.'.',
                    ]);
                }

                ProductFactory::new()->{$family}()
                    ->for($postType, 'type')->for($category, 'category')
                    ->count(10)
                    ->sequence(fn (Sequence $sequence) => ['sort_order' => $position + $sequence->index + 1])
                    ->create();
                $position += 10;
            }
        });
    }
}
