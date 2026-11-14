<?php

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Maxi032\LaravelAdminPackage\Tests\PostOptionsTestCase;

require_once __DIR__.'/PostOptionsTestCase.php';

uses(PostOptionsTestCase::class);

it('validates post type selections and can replace or clear them', function () {
    $page = PostType::create(['type' => 'page']);
    $faq = PostType::create(['type' => 'faq']);
    $faq->delete();
    $store = route('admin:settings.post-options.store');
    $data = [
        'key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'sort_order' => 0,
        'enabled' => 1, 'is_translatable' => 0, 'visible_in_post' => 1,
    ];

    $this->postJson($store, $data)->assertUnprocessable()->assertJsonValidationErrors('post_type_ids');
    $this->postJson($store, $data + ['post_type_ids' => [1, '1', 999, $faq->id]])
        ->assertUnprocessable()->assertJsonValidationErrors([
            'post_type_ids.0', 'post_type_ids.1', 'post_type_ids.2', 'post_type_ids.3',
        ]);
    expect(PostOption::count())->toBe(0);

    $this->post($store, $data + ['post_type_ids' => [1, $page->id]])->assertSessionHasNoErrors();
    $option = PostOption::sole();
    $edit = route('admin:settings.post-options.edit', $option);
    $update = route('admin:settings.post-options.update', $option);
    $checkboxes = static function ($response) {
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());

        return array_map(
            fn ($field) => (int) $field->getAttribute('value'),
            iterator_to_array((new \DOMXPath($document))->query('//input[@name="post_type_ids[]" and @checked]')),
        );
    };

    $editForm = $this->get($edit)->assertOk();
    $editForm->assertSeeInOrder(['name="post_types_present"', '<legend', 'name="post_type_ids[]"'], false);
    expect($option->postTypes()->orderBy('post_types.id')->pluck('post_types.id')->all())->toBe([1, $page->id])
        ->and($page->postOptions()->sole()->is($option))->toBeTrue()
        ->and($checkboxes($editForm))->toBe([1, $page->id]);

    $this->from($edit)->put($update, ['sort_order' => 0, 'post_type_ids' => [(string) $page->id]])
        ->assertSessionHasErrors('label');
    expect($checkboxes($this->get($edit)->assertOk()))->toBe([$page->id]);
    $this->from($edit)->put($update, ['sort_order' => 0, 'post_types_present' => 1])->assertSessionHasErrors('label');
    expect($checkboxes($this->get($edit)->assertOk()))->toBe([]);

    $this->put($update, $data + ['post_type_ids' => [$page->id]])->assertSessionHasNoErrors();
    expect($option->postTypes()->sole()->is($page))->toBeTrue();
    $this->put($update, $data + ['post_types_present' => 1])->assertSessionHasNoErrors();
    expect($option->postTypes()->exists())->toBeFalse();
});

it('filters post forms and writes by post type while retaining saved values', function () {
    $page = PostType::create(['type' => 'page']);
    $faq = PostType::create(['type' => 'faq']);
    DB::table('category_types')->insert(['id' => 3, 'type' => 'faq']);
    DB::table('categories')->insert(['id' => 3, 'type_id' => 3]);
    $option = PostOption::create(['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'visible_in_post' => true]);
    $option->postTypes()->attach([1, $page->id]);
    $field = 'options[subtitle]';
    $this->get(route('admin:posts.create', 'blog'))->assertOk()->assertSee($field);
    $this->get(route('admin:posts.create', 'page'))->assertOk()->assertSee($field);
    $this->get(route('admin:posts.create', 'faq'))->assertOk()->assertDontSee($field);
    $this->getJson(route('admin:posts.type-fields', 'page'))->assertOk()
        ->assertJsonStructure(['categories', 'options']);
    $this->getJson(route('admin:posts.type-fields', 'faq'))->assertOk()
        ->assertJsonPath('options', fn ($html) => !str_contains($html, $field));

    $data = $this->postData(['subtitle' => 'Original subtitle']);
    $store = route('admin:posts.store');
    $faqData = array_replace($data, ['type_id' => $faq->id, 'category_id' => 3]);
    $this->postJson($store, $faqData)->assertUnprocessable()->assertJsonValidationErrors('options');
    expect(fn () => app(PostRepositoryInterface::class)->createPostWithTranslations($faqData))
        ->toThrow(ValidationException::class);
    expect(Post::count())->toBe(0);

    $this->post($store, $data)->assertSessionHasNoErrors();
    $post = Post::sole();
    $edit = route('admin:posts.edit', $post);
    $update = route('admin:posts.update', $post);
    $this->get($edit)->assertOk()->assertSee('Original subtitle');
    $this->putJson($update, $faqData)->assertUnprocessable()->assertJsonValidationErrors('options');
    expect(fn () => app(PostRepositoryInterface::class)->updatePostWithTranslations($post, $faqData))
        ->toThrow(ValidationException::class);
    expect((int) $post->fresh()->type_id)->toBe(1);

    $this->put(route('admin:settings.post-options.update', $option), [
        'label' => 'Subtitle', 'sort_order' => 0, 'post_type_ids' => [$page->id],
    ])->assertSessionHasNoErrors();
    $this->get($edit)->assertOk()->assertDontSee($field);
    $this->putJson($update, $data)->assertUnprocessable()->assertJsonValidationErrors('options');
    $this->put($update, array_replace($data, ['options' => []]))->assertSessionHasNoErrors();
    expect($post->fresh()->option('subtitle'))->toBeNull()
        ->and($post->optionValues()->sole()->text_value)->toBe('Original subtitle');

    $option->postTypes()->attach(1);
    $this->get($edit)->assertOk()->assertSee('Original subtitle');
});

it('backfills legacy availability and cascades hard deletes on the pivot', function () {
    $page = PostType::create(['type' => 'page']);
    $page->delete();
    $legacy = PostOption::create(['key' => 'legacy', 'label' => 'Legacy', 'type' => 'text', 'visible_in_post' => true]);
    $migration = require __DIR__.'/../database/migrations/2026_11_08_000008_create_post_type_options_table.php';
    $migration->down();
    $migration->up();
    expect($legacy->postTypes()->withTrashed()->orderBy('post_types.id')->pluck('post_types.id')->all())
        ->toBe([1, $page->id]);
    expect(fn () => $legacy->postTypes()->attach(1))->toThrow(UniqueConstraintViolationException::class);
    $page->forceDelete();
    expect(DB::table('post_type_options')->count())->toBe(1);
    $legacy->forceDelete();
    expect(DB::table('post_type_options')->count())->toBe(0);
});
