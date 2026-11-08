<?php

namespace Maxi032\LaravelAdminPackage\Tests;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Models\PostOptionValue;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Tests\TestCase;

class PostOptionsTest extends TestCase
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

    public function test_options_render_and_round_trip_on_post_forms(): void
    {
        $this->createSampleOptions();
        $existing = Post::create(['type_id' => 1, 'category_id' => 1, 'status' => 1]);
        self::assertFalse($existing->option('featured'));
        self::assertNull($existing->option('reading_time'));
        $this->get(route('admin:posts.create', ['type' => 'blog']))->assertOk()
            ->assertSee('options[featured]')->assertSee('options[subtitle][nl]');

        $this->post(route('admin:posts.store'), $this->postData([
            'featured' => '1', 'reading_time' => '8', 'sticky_in_category' => '1',
            'subtitle' => ['en' => 'English subtitle', 'nl' => 'Nederlandse ondertitel'],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin:posts.type.list', 'blog'));

        $post = Post::latest('id')->firstOrFail();
        self::assertTrue($post->option('featured'));
        self::assertSame(8, $post->option('reading_time'));
        self::assertSame('Nederlandse ondertitel', $post->option('subtitle', 'nl'));
        self::assertSame([$post->id], Post::featured()->pluck('id')->all());
        self::assertSame([$post->id, $existing->id], Post::inCategoryListing(1)->pluck('id')->all());
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertSee('Nederlandse ondertitel');

        $this->put(route('admin:posts.update', $post), $this->postData([
            'featured' => '0', 'reading_time' => '', 'subtitle' => ['nl' => ''],
        ]))->assertSessionHasNoErrors();

        $post->refresh();
        self::assertFalse($post->option('featured'));
        self::assertNull($post->option('reading_time'));
        self::assertTrue($post->option('sticky_in_category'));
        self::assertSame('English subtitle', $post->option('subtitle', 'nl'));
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertDontSee('Nederlandse ondertitel');

        $this->delete(route('admin:posts.destroy', $post))->assertSessionHasNoErrors();
        self::assertSame(3, $post->optionValues()->count());
        $this->post(route('admin:posts.recover', $post))->assertSessionHasNoErrors();
        self::assertSame('English subtitle', $post->fresh()->option('subtitle', 'en'));
        $this->delete(route('admin:posts.destroy', $post))->assertSessionHasNoErrors();
        $this->delete(route('admin:posts.force_delete', $post))->assertSessionHasNoErrors();
        self::assertSame(0, $post->optionValues()->count());
    }

    public function test_settings_manage_custom_fields_and_protect_saved_values(): void
    {
        $index = route('admin:settings.post-options.index');
        $create = route('admin:settings.post-options.create');
        $this->get($index)->assertOk()->assertSee($create);
        $this->get($create)->assertOk()
            ->assertSee('id="post-option-eager-loading" name="is_eager_loaded"', false)
            ->assertSee('for="post-option-eager-loading"', false);

        $data = [
            'key' => 'byline', 'label' => 'Byline', 'type' => 'text',
            'is_translatable' => '0', 'enabled' => '1', 'sort_order' => '50', 'visible_in_post' => '1',
            'is_eager_loaded' => '1',
        ];
        $this->post($index, $data)->assertSessionHasNoErrors()->assertRedirect($index);
        $option = PostOption::where('key', 'byline')->firstOrFail();
        self::assertTrue($option->is_eager_loaded);
        $this->get(route('admin:settings.post-options.edit', $option))->assertOk()->assertSee('Byline')
            ->assertSee('id="post-option-eager-loading" name="is_eager_loaded"', false);

        $this->post(route('admin:posts.store'), $this->postData(['byline' => 'Editorial team']))
            ->assertSessionHasNoErrors();
        $post = Post::firstOrFail();
        $update = route('admin:settings.post-options.update', $option);
        $this->put($update, array_replace($data, ['type' => 'integer']))->assertSessionHasErrors('type');
        self::assertSame('text', $option->fresh()->type);
        $this->put($update, array_replace($data, ['enabled' => '0']))->assertSessionHasNoErrors();
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertDontSee('options[byline]');
        self::assertNull($post->option('byline'));
        $this->put($update, array_replace($data, ['is_eager_loaded' => '0']))->assertSessionHasNoErrors();
        self::assertFalse($option->fresh()->is_eager_loaded);
        self::assertSame('Editorial team', $post->option('byline'));
    }

    public function test_invalid_options_and_failed_option_saves_do_not_change_the_post(): void
    {
        $this->createSampleOptions();
        $create = route('admin:posts.create');
        $store = route('admin:posts.store');
        $this->from($create)->post($store, $this->postData([
            'reading_time' => '0', 'subtitle' => ['xx' => 'Invalid language'],
        ]))->assertSessionHasErrors(['options.reading_time', 'options.subtitle']);
        $this->get($create)->assertOk()->assertSee('nav-options-tab');
        $this->post($store, $this->postData(['unregistered' => 'value']))->assertSessionHasErrors('options');
        $invalidTranslation = $this->postData(['subtitle' => ['nl' => ['Invalid text']]]);
        $this->postJson($store, $invalidTranslation)->assertUnprocessable()
            ->assertJsonValidationErrors('options.subtitle.nl');

        try {
            app(PostRepositoryInterface::class)->createPostWithTranslations($invalidTranslation);
            self::fail('The post save accepted an invalid translated option.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('options.subtitle.nl', $exception->errors());
        }
        self::assertSame(0, Post::count());
        self::assertSame(0, DB::table('post_translations')->count());

        PostOptionValue::saving(static fn () => false);
        try {
            $this->post($store, $this->postData(['featured' => '1']))->assertSessionHasErrors('post');
            self::assertSame(0, Post::count());
            self::assertSame(0, DB::table('post_translations')->count());
        } finally {
            PostOptionValue::flushEventListeners();
        }
    }

    public function test_legacy_options_can_be_toggled_and_edited(): void
    {
        $option = PostOption::create(['key' => 'featured', 'label' => 'Featured', 'type' => 'boolean', 'enabled' => true]);
        $option->forceFill(['is_builtin' => true])->save();
        $index = route('admin:settings.post-options.index');
        $url = route('admin:settings.post-options.change-status', $option);

        $this->get($index)->assertOk()->assertSee('role="switch"', false)->assertSee($url)->assertDontSee('Built-in');
        $this->postJson($url, ['status' => 'enabled'])->assertUnprocessable()->assertJsonValidationErrors('status');
        self::assertTrue($option->fresh()->enabled);

        $this->postJson($url, ['status' => 0])->assertOk()->assertJsonStructure(['success']);
        self::assertFalse($option->fresh()->enabled);
        $this->postJson($url, ['status' => 1])->assertOk()->assertJsonStructure(['success']);
        self::assertTrue($option->fresh()->enabled);

        $this->put(route('admin:settings.post-options.update', $option), [
            'label' => 'Featured', 'sort_order' => 0, 'enabled' => '0',
            'type' => 'text', 'is_translatable' => '1',
        ])->assertSessionHasNoErrors();
        $option->refresh();
        self::assertSame('text', $option->type);
        self::assertTrue($option->is_translatable);
        self::assertFalse($option->enabled);
    }

    public function test_boolean_defaults_apply_to_unset_posts_and_respect_saved_false(): void
    {
        $this->createSampleOptions();
        $featured = PostOption::where('key', 'featured')->firstOrFail();
        $sticky = PostOption::where('key', 'sticky_in_category')->firstOrFail();
        foreach ([$featured, $sticky] as $option) {
            $edit = route('admin:settings.post-options.edit', $option);
            $this->get($edit)->assertOk()->assertSee('id="post-option-boolean-default" class="mb-3" >', false);
            $this->put(route('admin:settings.post-options.update', $option), [
                'label' => $option->label, 'sort_order' => $option->sort_order, 'boolean_default' => '1',
            ])->assertSessionHasNoErrors();
            self::assertTrue($option->fresh()->defaultValue());
        }
        $this->get(route('admin:settings.post-options.create'))->assertOk()
            ->assertSee('id="post-option-boolean-default" class="mb-3"  hidden', false);

        $this->post(route('admin:posts.store'), $this->postData([
            'featured' => '0', 'sticky_in_category' => '0',
        ]))->assertSessionHasNoErrors();
        $explicitlyOff = Post::firstOrFail();
        $unset = Post::create(['type_id' => 1, 'category_id' => 1, 'status' => 1, 'sort_order' => 100]);
        self::assertFalse($explicitlyOff->option('featured'));
        self::assertTrue($unset->option('featured'));
        self::assertSame([$unset->id], Post::featured()->pluck('id')->all());
        self::assertSame([$unset->id, $explicitlyOff->id], Post::inCategoryListing(1)->pluck('id')->all());

        $featured->update(['visible_in_post' => false]);
        self::assertSame([$explicitlyOff->id, $unset->id], Post::featured()->orderBy('id')->pluck('id')->all());
    }

    public function test_options_are_settings_only_until_made_visible_on_posts(): void
    {
        $settings = route('admin:settings.post-options.index');
        $create = route('admin:posts.create');
        $store = route('admin:posts.store');
        $data = [
            'key' => 'eager_loaded', 'label' => 'Eager loaded', 'type' => 'boolean',
            'is_translatable' => '0', 'enabled' => '1', 'sort_order' => '50', 'boolean_default' => '1',
        ];
        $this->post($settings, $data)->assertSessionHasNoErrors();
        $option = PostOption::where('key', 'eager_loaded')->firstOrFail();
        $update = route('admin:settings.post-options.update', $option);
        self::assertFalse($option->visible_in_post);
        $this->get(route('admin:settings.post-options.edit', $option))->assertOk()->assertSee('Visible in post');
        $this->get($create)->assertOk()->assertDontSee('options[eager_loaded]');
        $this->post($store, $this->postData(['eager_loaded' => '0']))->assertSessionHasErrors('options');

        $this->put($update, $data + ['visible_in_post' => '1'])->assertSessionHasNoErrors();
        $this->get($create)->assertOk()->assertSee('options[eager_loaded]');
        $this->post($store, $this->postData(['eager_loaded' => '0']))->assertSessionHasNoErrors();
        $post = Post::firstOrFail();
        self::assertFalse($post->option('eager_loaded'));

        $this->put($update, $data + ['visible_in_post' => '0'])->assertSessionHasNoErrors();
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertDontSee('options[eager_loaded]');
        self::assertTrue($post->option('eager_loaded'));
        self::assertSame(1, $option->values()->count());
        $this->put($update, $data + ['visible_in_post' => '1'])->assertSessionHasNoErrors();
        self::assertFalse($post->option('eager_loaded'));
    }

    public function test_fresh_install_can_create_posts_without_any_option_definitions(): void
    {
        self::assertSame(0, PostOption::count());
        $this->get(route('admin:settings.post-options.index'))->assertOk()->assertSee('No post options found.');
        $this->get(route('admin:posts.create'))->assertOk();

        $data = $this->postData([]);
        unset($data['options']);
        $this->post(route('admin:posts.store'), $data)->assertSessionHasNoErrors();
        self::assertSame(1, Post::count());
        self::assertFalse(Post::featured()->exists());
        self::assertSame(1, Post::inCategoryListing(1)->count());
    }

    public function test_post_options_can_be_trashed_recovered_and_permanently_deleted(): void
    {
        $settings = route('admin:settings.post-options.index');
        $trash = route('admin:settings.post-options.trash');
        $data = [
            'key' => 'byline', 'label' => 'Editorial byline', 'type' => 'text',
            'is_translatable' => '0', 'enabled' => '1', 'sort_order' => '50', 'visible_in_post' => '1',
        ];
        $this->post($settings, $data)->assertSessionHasNoErrors();
        $option = PostOption::where('key', 'byline')->firstOrFail();
        $delete = route('admin:settings.post-options.destroy', $option);
        $recover = route('admin:settings.post-options.recover', $option);
        $forceDelete = route('admin:settings.post-options.force_delete', $option);
        $this->get($settings)->assertOk()->assertSee($delete)->assertSee($trash);
        $this->delete($forceDelete)->assertNotFound();
        $this->post($recover)->assertNotFound();

        $this->post(route('admin:posts.store'), $this->postData(['byline' => 'Editorial team']))
            ->assertSessionHasNoErrors();
        $post = Post::firstOrFail();
        $this->delete($delete)->assertRedirect($settings)->assertSessionHasNoErrors();
        $this->assertSoftDeleted($option);
        $this->get($settings)->assertOk()->assertDontSee($option->label);
        $this->get($trash)->assertOk()->assertSee($option->label)->assertSee($recover)->assertSee($forceDelete);
        $this->get(route('admin:settings.post-options.edit', $option))->assertNotFound();
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertDontSee('options[byline]');
        $this->post($settings, $data)->assertSessionHasErrors('key');
        self::assertNull($post->option('byline'));
        self::assertSame(1, $option->values()->count());

        $this->post($recover)->assertRedirect($trash)->assertSessionHasNoErrors();
        self::assertSame('Editorial team', $post->option('byline'));
        $this->delete($delete)->assertSessionHasNoErrors();

        PostOption::forceDeleting(static fn () => false);
        try {
            $this->delete($forceDelete)->assertRedirect($trash)->assertSessionHasErrors('option');
            $this->assertSoftDeleted($option);
            self::assertSame(1, $option->values()->count());
        } finally {
            PostOption::flushEventListeners();
        }

        $this->delete($forceDelete)->assertRedirect($trash)->assertSessionHasNoErrors();
        $this->assertModelMissing($option);
        self::assertSame(0, $option->values()->count());
        $this->assertModelExists($post);
        $this->post($settings, $data)->assertSessionHasNoErrors();
    }

    public function test_overview_and_trash_paginate_html_and_ajax_requests(): void
    {
        for ($position = 1; $position <= 11; $position++) {
            PostOption::create([
                'key' => 'active_'.$position, 'label' => sprintf('Active option %02d', $position),
                'type' => 'text', 'sort_order' => $position,
            ]);
            PostOption::create([
                'key' => 'trashed_'.$position, 'label' => sprintf('Trashed option %02d', $position),
                'type' => 'text',
            ])->delete();
        }

        foreach ([
            ['index', 'Active option 01', 'Active option 11', 'Trashed option'],
            ['trash', 'Trashed option 11', 'Trashed option 01', 'Active option'],
        ] as [$view, $firstPage, $secondPage, $excluded]) {
            $url = route('admin:settings.post-options.'.$view);
            $this->get($url.'?filter=example')->assertOk()->assertSee($firstPage)
                ->assertDontSee($secondPage)->assertDontSee($excluded)
                ->assertSee('filter=example&amp;page=2', false);
            $this->get($url.'?page=2')->assertOk()->assertSee($secondPage)->assertDontSee($firstPage);

            $response = $this->getJson($url.'?page=2', ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()->assertJsonPath('pagination', [
                    'current_page' => 2, 'last_page' => 2, 'per_page' => 10, 'total' => 11,
                ]);
            self::assertStringContainsString($secondPage, $response->json('html'));
            self::assertStringNotContainsString($firstPage, $response->json('html'));
        }
    }

    private function createSampleOptions(): void
    {
        foreach ([
            'featured' => 'boolean',
            'reading_time' => 'integer',
            'sticky_in_category' => 'boolean',
            'subtitle' => 'text',
        ] as $key => $type) {
            PostOption::create([
                'key' => $key,
                'label' => $key,
                'type' => $type,
                'is_translatable' => $key === 'subtitle',
                'visible_in_post' => true,
            ]);
        }
    }

    private function postData(array $options): array
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
