<?php

namespace Maxi032\LaravelAdminPackage\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Models\PostOptionValue;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
require_once __DIR__.'/PostOptionsTestCase.php';

class PostOptionsTest extends PostOptionsTestCase
{
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
            'key' => 'by line', 'label' => 'Byline', 'type' => 'text',
            'post_type_ids' => [1],
            'is_translatable' => '0', 'enabled' => '1', 'sort_order' => '50', 'visible_in_post' => '1',
            'is_eager_loaded' => '1',
        ];
        $this->post($index, $data)->assertSessionHasNoErrors()->assertRedirect($index);
        $option = PostOption::where('key', 'by_line')->firstOrFail();
        $this->postJson($index, array_replace($data, ['key' => 'by_line']))->assertUnprocessable()->assertJsonValidationErrors('key');
        self::assertTrue($option->is_eager_loaded);
        $this->get(route('admin:settings.post-options.edit', $option))->assertOk()->assertSee('Byline')
            ->assertSee('id="post-option-eager-loading" name="is_eager_loaded"', false);

        $this->post(route('admin:posts.store'), $this->postData(['by_line' => 'Editorial team']))
            ->assertSessionHasNoErrors();
        $post = Post::firstOrFail();
        $update = route('admin:settings.post-options.update', $option);
        $this->put($update, array_replace($data, ['type' => 'integer']))->assertSessionHasErrors('type');
        self::assertSame('text', $option->fresh()->type);
        $this->put($update, array_replace($data, ['enabled' => '0']))->assertSessionHasNoErrors();
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertDontSee('options[by_line]');
        self::assertNull($post->option('by_line'));
        $this->put($update, array_replace($data, ['is_eager_loaded' => '0']))->assertSessionHasNoErrors();
        self::assertFalse($option->fresh()->is_eager_loaded);
        self::assertSame('Editorial team', $post->option('by_line'));
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

        $featured->postTypes()->detach(1);
        self::assertFalse(Post::featured()->exists());
        self::assertNull($unset->option('featured'));
        self::assertSame(1, $featured->values()->count());
    }

    public function test_options_are_settings_only_until_made_visible_on_posts(): void
    {
        $settings = route('admin:settings.post-options.index');
        $create = route('admin:posts.create');
        $store = route('admin:posts.store');
        $data = [
            'key' => 'eager_loaded', 'label' => 'Eager loaded', 'type' => 'boolean',
            'post_type_ids' => [1],
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
        $this->get(route('admin:posts.create', 'blog'))->assertOk()->assertSee('options[eager_loaded]');
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
            'post_type_ids' => [1],
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
        $status = route('admin:settings.post-options.change-status', $option);
        $this->postJson($status, ['status' => 0])->assertUnprocessable()->assertJsonValidationErrors('status');
        self::assertTrue($option->fresh()->enabled);
        $this->delete($delete)->assertRedirect($settings)->assertSessionHasErrors('option');
        $this->assertNotSoftDeleted($option);

        // Simulate an option moved to Trash under the old rules.
        $option->delete();
        $trashHtml = new \DOMDocument();
        @$trashHtml->loadHTML($this->get($trash)->assertOk()->getContent());
        self::assertSame(1, (new \DOMXPath($trashHtml))->query('//button[@data-delete-url and @disabled]')->length);
        $this->delete($forceDelete)->assertRedirect($trash)->assertSessionHasErrors('option');
        self::assertSame(1, $option->values()->count());
        $this->post($recover)->assertRedirect($trash)->assertSessionHasNoErrors();
        self::assertSame('Editorial team', $post->option('byline'));
        $option->values()->delete();

        $this->delete($delete)->assertRedirect($settings)->assertSessionHasNoErrors();
        $this->assertSoftDeleted($option);
        $this->get($settings)->assertOk()->assertDontSee($option->label);
        $this->get($trash)->assertOk()->assertSee($option->label)->assertSee($recover)->assertSee($forceDelete);
        $this->get(route('admin:settings.post-options.edit', $option))->assertNotFound();
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertDontSee('options[byline]');
        $this->post($settings, $data)->assertSessionHasErrors('key');
        self::assertNull($post->option('byline'));

        $this->post($recover)->assertRedirect($trash)->assertSessionHasNoErrors();
        $this->delete($delete)->assertSessionHasNoErrors();

        PostOption::forceDeleting(static fn () => false);
        try {
            $this->delete($forceDelete)->assertRedirect($trash)->assertSessionHasErrors('option');
            $this->assertSoftDeleted($option);
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

    public function test_overview_counts_each_post_with_a_saved_option_value_once(): void
    {
        $this->createSampleOptions();
        $subtitle = PostOption::where('key', 'subtitle')->firstOrFail();
        $post = Post::create(['type_id' => 1, 'category_id' => 1, 'status' => 1]);
        $post->translations()->create(['language' => 'en', 'title' => 'An <example>', 'slug' => 'example', 'content' => 'Text']);
        $post->optionValues()->createMany([
            ['post_option_id' => $subtitle->id, 'locale' => 'en', 'text_value' => 'Subtitle'],
            ['post_option_id' => $subtitle->id, 'locale' => 'nl', 'text_value' => 'Ondertitel'],
        ]);
        $trashed = Post::create(['type_id' => 1, 'category_id' => 1, 'status' => 1]);
        $trashed->optionValues()->create(['post_option_id' => $subtitle->id, 'locale' => 'en', 'text_value' => 'Archived']);
        $trashed->delete();

        $response = $this->get(route('admin:settings.post-options.index'))->assertOk()->assertSee('<th scope="col">Posts</th>', false);
        $counts = $response->viewData('options')->pluck('posts_count', 'key');
        self::assertSame(2, $counts['subtitle']);
        self::assertSame(0, $counts['featured']);

        $html = new \DOMDocument();
        @$html->loadHTML($response->getContent());
        $xpath = new \DOMXPath($html);
        $usedRow = '//tr[th[normalize-space()="subtitle"]]';
        $unusedRow = '//tr[th[normalize-space()="featured"]]';
        self::assertSame(1, $xpath->query($usedRow.'//input[@role="switch" and @disabled and not(@data-status-toggle)]')->length);
        self::assertSame(1, $xpath->query($usedRow.'//button[@data-delete-url and @disabled]')->length);
        self::assertSame(1, $xpath->query($unusedRow.'//input[@data-status-toggle and not(@disabled)]')->length);
        self::assertSame(1, $xpath->query($unusedRow.'//button[@data-delete-url and not(@disabled)]')->length);

        $listUrl = route('admin:settings.post-options.posts', $subtitle);
        $response->assertSee('popovertarget="option-posts-'.$subtitle->id.'"', false)->assertSee($listUrl);
        $editUrl = route('admin:posts.edit', $post);
        $trashUrl = route('admin:posts.trash', ['type' => 'blog', 'post_id' => $trashed->id]);
        $list = $this->get($listUrl)->assertOk()->assertSeeInOrder([
            'aria-label="Search posts"', 'data-post-search-item', '<strong>Blog</strong>', 'An &lt;example&gt;',
            'Post ID', 'href="'.$editUrl.'"', 'href="'.$trashUrl.'"',
        ], false);
        self::assertSame(1, substr_count($list->getContent(), $editUrl));
        $this->get($trashUrl)->assertOk()->assertViewHas('posts', fn ($posts) => $posts->pluck('id')->all() === [$trashed->id]);
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
            ])->postTypes()->attach(1);
        }
    }

}
