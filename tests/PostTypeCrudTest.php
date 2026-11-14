<?php

namespace Maxi032\LaravelAdminPackage\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;

require_once __DIR__.'/PostOptionsTestCase.php';

class PostTypeCrudTest extends PostOptionsTestCase
{
    public function test_post_types_can_be_managed_from_settings_and_used_in_the_editor(): void
    {
        $index = route('admin:settings.post-types.index');
        $this->get($index)->assertOk()->assertSeeInOrder([
            $index, route('admin:settings.post-options.index'), '<code>blog</code>',
        ], false);
        $this->getJson($index, ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('pagination.total', 1);
        $this->get(route('admin:settings.post-types.create'))->assertOk()->assertSee('Add post type');
        $this->get(route('admin:posts.create', 'blog'))->assertOk();

        $store = route('admin:settings.post-types.store');
        $this->postJson($store, ['type' => 'blog', 'status' => '1'])->assertUnprocessable()->assertJsonValidationErrors('type');
        $this->postJson($store, ['type' => 'create', 'status' => '1'])->assertUnprocessable()->assertJsonValidationErrors('type');
        $this->post($store, ['type' => 'local-news', 'status' => '1'])->assertSessionHasNoErrors()->assertRedirect($index);
        $this->postJson($store, ['type' => 'local_news', 'status' => '1'])->assertUnprocessable()->assertJsonValidationErrors('type');

        $type = PostType::where('type', 'local_news')->firstOrFail();
        $categoryType = CategoryType::where('type', 'local_news')->firstOrFail();
        $this->get(route('admin:categories.type.list', 'local_news'))->assertOk();
        $this->get(route('admin:posts.create', 'local_news'))->assertOk();
        $this->get(route('admin:settings.post-options.create'))->assertOk()->assertSee('post-option-post-type-'.$type->id);
        $this->get(route('admin:settings.post-types.edit', $type))->assertOk()->assertSee('Edit post type');

        $category = Category::create(['type_id' => $categoryType->id, 'status' => true]);
        $post = Post::create(['type_id' => $type->id, 'category_id' => $category->id, 'status' => 1]);
        $update = route('admin:settings.post-types.update', $type);
        $this->put($update, ['type' => 'news-articles', 'status' => '1'])->assertSessionHasNoErrors()->assertRedirect($index);
        $this->assertDatabaseHas('post_types', ['id' => $type->id, 'type' => 'news_articles', 'status' => 1]);
        $this->assertDatabaseHas('category_types', ['id' => $categoryType->id, 'type' => 'news_articles']);
        $this->get(route('admin:posts.create', 'local_news'))->assertNotFound();
        $this->get(route('admin:posts.edit', $post))->assertOk()->assertSee('news_articles');
        $this->put(route('admin:posts.update', $post), array_replace($this->postData([]), [
            'type_id' => $type->id, 'category_id' => $category->id,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin:posts.type.list', 'news_articles'));

        $this->delete(route('admin:settings.post-types.destroy', $type))->assertSessionHasErrors('type');
        $this->assertNotSoftDeleted($type);
    }

    public function test_overview_counts_active_and_trashed_posts_and_disables_delete(): void
    {
        $post = Post::create(['type_id' => 1, 'category_id' => 1, 'status' => 1]);
        $post->translations()->create(['language' => 'en', 'title' => 'Example post', 'slug' => 'example', 'content' => 'Text']);
        $trashed = Post::create(['type_id' => 1, 'category_id' => 1, 'status' => 1]);
        $trashed->delete();
        $emptyType = PostType::create(['type' => 'empty']);
        $response = $this->get(route('admin:settings.post-types.index'))->assertOk();

        $types = $response->viewData('postTypes')->keyBy('id');
        self::assertSame(2, $types[$post->type_id]->posts_count);
        self::assertSame(0, $types[$emptyType->id]->posts_count);
        $postsUrl = route('admin:settings.post-types.posts', $post->type_id);
        $response->assertSee('popovertarget="type-posts-1"', false)->assertSee($postsUrl)
            ->assertDontSee('popovertarget="type-posts-'.$emptyType->id.'"', false);
        $this->get($postsUrl)->assertOk()->assertSeeInOrder([
            'aria-label="Search posts"', 'data-post-search-item', '<strong>Blog</strong>', 'Example post',
            'Post ID', route('admin:posts.edit', $post),
            route('admin:posts.trash', ['type' => 'blog', 'post_id' => $trashed->id]),
        ], false);
        $html = new \DOMDocument;
        @$html->loadHTML($response->getContent());
        $xpath = new \DOMXPath($html);
        $buttons = $xpath->query('//button[@data-delete-url]');
        self::assertSame(2, $buttons->length);
        self::assertTrue($buttons->item(0)->hasAttribute('disabled'));
        self::assertFalse($buttons->item(1)->hasAttribute('disabled'));
        self::assertSame(1, $xpath->query('//input[@role="switch" and @disabled and not(@data-status-toggle)]')->length);
        self::assertSame(1, $xpath->query('//input[@role="switch" and not(@disabled) and @data-status-toggle]')->length);
    }

    public function test_a_type_with_posts_cannot_be_deactivated_by_the_switch_or_edit_form(): void
    {
        $type = PostType::findOrFail(1);
        Post::create(['type_id' => $type->id, 'category_id' => 1, 'status' => 1])->delete();
        $switch = route('admin:settings.post-types.change-status', $type);

        $this->postJson($switch, ['status' => '1'])->assertOk();
        $this->postJson($switch, ['status' => '0'])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->putJson(route('admin:settings.post-types.update', $type), ['type' => 'blog', 'status' => '0'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertDatabaseHas('post_types', ['id' => $type->id, 'status' => 1]);

        $response = $this->get(route('admin:settings.post-types.edit', $type))->assertOk();
        $html = new \DOMDocument;
        @$html->loadHTML($response->getContent());
        self::assertSame(1, (new \DOMXPath($html))->query('//*[contains(concat(" ", normalize-space(@class), " "), " card-body ")]//input[@id="post-type-status"]')->length);
        self::assertTrue($html->getElementById('post-type-status')->hasAttribute('disabled'));
        self::assertTrue($html->getElementById('post-type-status')->hasAttribute('checked'));
    }

    public function test_delete_preserves_trashed_dependencies_and_removes_unused_types_from_selectors(): void
    {
        $type = PostType::findOrFail(1);
        $destroy = route('admin:settings.post-types.destroy', $type);
        Category::findOrFail(1)->delete();
        $this->delete($destroy)->assertSessionHasErrors('type');
        Category::withTrashed()->findOrFail(1)->forceDelete();

        $optionId = DB::table('post_option')->insertGetId([
            'key' => 'extra', 'label' => 'Extra', 'type' => 'text', 'deleted_at' => now(),
        ]);
        DB::table('post_type_options')->insert(['post_type_id' => 1, 'post_option_id' => $optionId]);
        $this->delete($destroy)->assertSessionHasErrors('type');
        DB::table('post_type_options')->delete();

        $this->get(route('admin:posts.create', 'blog'))->assertOk();
        $this->delete($destroy)->assertSessionHasNoErrors()->assertRedirect(route('admin:settings.post-types.index'));
        $this->assertSoftDeleted($type);
        $this->assertSoftDeleted('category_types', ['id' => 1]);
        $this->get(route('admin:posts.create', 'blog'))->assertNotFound();
        $this->get(route('admin:settings.post-types.edit', $type))->assertNotFound();
    }

    public function test_type_labels_are_localized_and_the_overview_switch_updates_status(): void
    {
        app('view')->getFinder()->prependNamespace('laravel-admin-package', __DIR__.'/../resources/views');
        $this->app->setLocale('nl');
        $index = route('admin:settings.post-types.index');
        $switch = route('admin:settings.post-types.change-status', 1);
        $this->put(route('admin:settings.post-types.update', 1), ['type' => 'products', 'status' => '0'])
            ->assertSessionHasNoErrors()->assertRedirect($index);
        self::assertFalse(Schema::hasColumn('post_types', 'name'));
        $this->get(route('admin:settings.post-types.edit', 1))->assertOk()
            ->assertDontSee('post-type-name-');
        $this->get($index)->assertOk()->assertSee($switch)->assertSee('data-status-toggle');
        $this->get(route('admin:posts.type.list', 'products'))->assertOk()
            ->assertSee('<h1 class="h3 mb-0">Producten</h1>', false)
            ->assertDontSee('<span class="sidebar-link-label">Producten</span>', false);
        $this->get(route('admin:posts.create', 'products'))->assertOk()->assertSee('>Producten</option>', false);
        $this->get(route('admin:settings.post-options.create'))->assertOk()->assertSee('>Producten</label>', false);
        PostType::create(['type' => 'release_notes', 'status' => true]);
        $this->get($index)->assertOk()->assertSee('<span class="sidebar-link-label">Release notes</span>', false);

        $this->postJson($switch, ['status' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->postJson($switch, ['status' => '1'])->assertOk()->assertJsonStructure(['success']);
        $this->assertDatabaseHas('post_types', ['id' => 1, 'type' => 'products', 'status' => 1]);
        $this->get($index)->assertOk()->assertSee('<span class="sidebar-link-label">Producten</span>', false)
            ->assertSee('id="post-type-menu-1"', false);
        $this->postJson($switch, ['status' => '0'])->assertOk();
        $this->assertDatabaseHas('post_types', ['id' => 1, 'status' => 0]);
        $this->get($index)->assertOk()->assertSee('<code>products</code>', false)
            ->assertDontSee('id="post-type-menu-1"', false);
    }

    public function test_the_selected_icon_is_validated_saved_and_displayed_in_the_sidebar(): void
    {
        app('view')->getFinder()->prependNamespace('laravel-admin-package', __DIR__.'/../resources/views');
        $store = route('admin:settings.post-types.store');
        $this->post($store, ['type' => 'events', 'status' => '1', 'icon' => 'cil-calendar'])->assertSessionHasNoErrors();
        $type = PostType::where('type', 'events')->firstOrFail();
        self::assertSame('cil-calendar', $type->icon);
        self::assertSame('cil-description', PostType::findOrFail(1)->icon);
        $this->get(route('admin:settings.post-types.edit', $type))->assertOk()
            ->assertSee('<option value="cil-calendar" selected>', false)
            ->assertSeeInOrder(['data-icon="cil-calendar"', '<span class="cil-calendar me-2" aria-hidden="true"></span>Calendar'], false)
            ->assertSee('data-icon="cil-airplane-mode"', false)->assertSee('Search icons');

        $update = route('admin:settings.post-types.update', $type);
        $this->putJson($update, ['type' => 'events', 'status' => '1', 'icon' => 'unknown-icon'])
            ->assertUnprocessable()->assertJsonValidationErrors('icon');
        $this->put($update, ['type' => 'events', 'status' => '1', 'icon' => 'cil-airplane-mode'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('post_types', ['id' => $type->id, 'icon' => 'cil-airplane-mode']);
        $this->get(route('admin:settings.post-types.index'))->assertOk()->assertSeeInOrder([
            '<span class="nav-icon cil-airplane-mode"',
            '<span class="sidebar-link-label">Events</span>',
            'aria-controls="post-type-menu-'.$type->id.'"',
            '<svg class="post-type-chevron"',
        ], false);
    }

    public function test_trash_recovers_and_permanently_deletes_post_types_safely(): void
    {
        $type = PostType::findOrFail(1);
        Category::findOrFail(1)->forceDelete();
        $index = route('admin:settings.post-types.index');
        $trash = route('admin:settings.post-types.trash');
        $delete = route('admin:settings.post-types.destroy', $type);
        $recover = route('admin:settings.post-types.recover', $type);
        $destroy = route('admin:settings.post-types.force_delete', $type);

        $this->get($index)->assertOk()->assertSee($trash);
        $this->post($recover)->assertNotFound();
        $this->delete($destroy)->assertNotFound();
        $this->delete($delete)->assertSessionHasNoErrors()->assertRedirect($index);
        $this->get($index)->assertOk()->assertDontSee('<code>blog</code>', false);
        $this->get($trash)->assertOk()->assertSee('<code>blog</code>', false)->assertSee($recover)->assertSee($destroy)
            ->assertSee('data-coreui-target="#destroy-post"', false);
        $this->getJson($trash, ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('pagination.total', 1);

        $this->post($recover)->assertSessionHasNoErrors()->assertRedirect($trash);
        $this->assertNotSoftDeleted($type);
        $this->assertNotSoftDeleted('category_types', ['id' => 1]);
        $this->get($trash)->assertOk()->assertSee('No deleted post types found.');
        $this->get(route('admin:categories.type.list', 'blog'))->assertOk();

        $this->delete($delete)->assertSessionHasNoErrors()->assertRedirect($index);
        $category = Category::create(['type_id' => 1, 'status' => true]);
        $post = Post::create(['type_id' => 1, 'category_id' => $category->id, 'status' => 1]);
        $post->delete();
        $this->delete($destroy)->assertSessionHasErrors('type')->assertRedirect($trash);
        $this->assertSoftDeleted($post);
        $post->forceDelete();
        $this->delete($destroy)->assertSessionHasErrors('type')->assertRedirect($trash);
        $category->forceDelete();

        $this->delete($destroy)->assertSessionHasNoErrors()->assertRedirect($trash);
        $this->assertDatabaseMissing('post_types', ['id' => 1]);
        $this->assertDatabaseMissing('category_types', ['id' => 1]);
        $this->post($recover)->assertNotFound();
    }
}
