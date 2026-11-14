<?php

namespace Maxi032\LaravelAdminPackage\Tests;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class PostOptionsTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.fallback_locale' => 'en',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'laravel-admin-package.allowed_languages' => [['code' => 'en'], ['code' => 'nl']],
        ]);
        $migrations = [
            '2023_08_01_074056_create_category_types_table.php',
            '2023_08_01_114056_create_categories_table.php',
            '2023_08_01_135109_create_category_translations_table.php',
            '2023_08_07_074056_create_post_types_table.php',
            '2023_08_07_114056_create_posts_table.php',
            '2023_08_07_135109_create_post_translations_table.php',
            '2023_09_29_110621_add_meta_tags_to_translations.php',
            '2023_10_10_144537_add_category_id_to_table_posts.php',
            '2026_10_25_000001_expand_post_status_values.php',
            '2026_10_25_000002_add_unique_post_language_to_post_translations.php',
            '2026_11_08_000001_create_post_option_tables.php',
            '2026_11_08_000002_add_boolean_default_to_post_option.php',
            '2026_11_08_000003_add_visible_in_post_to_post_option.php',
            '2026_11_08_000004_add_soft_deletes_to_post_option.php',
            '2026_11_08_000005_add_is_eagerloaded_to_post_option.php',
            '2026_11_08_000006_drop_help_text_from_post_option.php',
            '2026_11_08_000007_rename_is_eagerloaded_on_post_option.php',
            '2026_11_08_000008_create_post_type_options_table.php',
            '2026_11_14_000003_add_icon_to_post_types.php',
        ];
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => array_map(fn (string $migration) => __DIR__.'/../database/migrations/'.$migration, $migrations),
        ])->assertSuccessful();
        $this->withoutVite();

        $this->actingAs(new User(['name' => 'Editor']));
        DB::table('post_types')->insert(['id' => 1, 'type' => 'blog']);
        DB::table('category_types')->insert(['id' => 1, 'type' => 'blog']);
        DB::table('categories')->insert(['id' => 1, 'type_id' => 1]);
    }

    protected function tearDown(): void
    {
        // Package tests also construct paginators outside the booted application.
        Paginator::currentPageResolver(static fn () => 1);

        parent::tearDown();
    }

    protected function postData(array $options): array
    {
        return [
            'type_id' => 1, 'category_id' => 1, 'status' => 1,
            'translations' => [
                'title' => ['en' => 'Article', 'nl' => 'Artikel'],
                'slug' => ['en' => 'article', 'nl' => 'artikel'],
                'content' => ['en' => 'Text', 'nl' => 'Tekst'],
            ],
            'options' => $options,
        ];
    }
}
