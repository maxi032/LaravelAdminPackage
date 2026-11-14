<?php

namespace Maxi032\LaravelAdminPackage\Tests;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Models\{Post, Category, PostTranslation, CategoryTranslation};
use Maxi032\LaravelAdminPackage\Repositories\{PostRepository, CategoryRepository};
use Maxi032\LaravelAdminPackage\RepositoryServiceProvider;
use Maxi032\LaravelAdminPackage\Requests\{PostRequest, CategoryRequest};
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TranslationPersistenceTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        parent::setUp();
        Model::clearBootedModels();
        Facade::clearResolvedInstances();
        $this->app = new Application(dirname(__DIR__, 4));
        $this->app->instance('config', new Repository([
            'app' => ['locale' => 'en'],
            'laravel-admin-package' => ['allowed_languages' => [['code' => 'en'], ['code' => 'de']]],
            'view' => ['paths' => [], 'compiled' => sys_get_temp_dir()],
            'database' => ['default' => 'sqlite', 'connections' => [
                'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            ]],
            'logging' => ['default' => 'null', 'channels' => [
                'null' => ['driver' => 'monolog', 'handler' => \Monolog\Handler\NullHandler::class],
            ]],
        ]));
        Facade::setFacadeApplication($this->app);
        foreach ([
            \Illuminate\Events\EventServiceProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Database\DatabaseServiceProvider::class,
            \Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
            \Illuminate\Routing\RoutingServiceProvider::class,
            \Illuminate\Log\LogServiceProvider::class,
            RepositoryServiceProvider::class,
        ] as $provider) {
            $this->app->register($provider);
        }
        $this->app->instance('request', Request::create('http://localhost'));
        $this->app->boot();
        $schema = DB::connection()->getSchemaBuilder();
        foreach (['post_types', 'category_types'] as $tableName) {
            $schema->create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->softDeletes();
            });
            DB::table($tableName)->insert(['id' => 1, 'type' => 'products']);
        }
        foreach (['post', 'category'] as $feature) {
            $schema->create($feature === 'post' ? 'posts' : 'categories', function (Blueprint $table) use ($feature) {
                $table->id();
                $table->unsignedBigInteger('type_id');
                if ($feature === 'post') {
                    $table->unsignedBigInteger('category_id');
                }
                $table->integer('status');
                $table->unsignedInteger('sort_order');
                $table->timestamps();
                $table->softDeletes();
            });
            $schema->create($feature.'_translations', function (Blueprint $table) use ($feature) {
                $table->id();
                $table->foreignId($feature.'_id')->constrained($feature === 'post' ? 'posts' : 'categories');
                $table->string('language');
                foreach (['title', 'slug', 'content', 'excerpt', 'meta_title', 'meta_keywords', 'meta_description'] as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
                $table->softDeletes();
                $table->unique([$feature.'_id', 'language']);
            });
        }
    }

    protected function tearDown(): void
    {
        foreach ([Post::class, Category::class, PostTranslation::class, CategoryTranslation::class] as $model) {
            $model::flushEventListeners();
        }
        $this->app['db']->disconnect();
        Model::unsetConnectionResolver();
        Model::unsetEventDispatcher();
        Model::clearBootedModels();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        $this->app->flush();
        parent::tearDown();
    }

    public static function features(): array
    {
        return ['posts' => ['Post'], 'categories' => ['Category']];
    }

    private function data(): array
    {
        return [
            'type_id' => 1, 'category_id' => 1, 'status' => 1,
            'translations' => [
                'title' => ['en' => 'Original English', 'de' => 'Original German'],
                'slug' => ['en' => 'english', 'de' => 'german'],
                'content' => ['en' => '<p>English</p>', 'de' => '<p>German</p>'],
                'excerpt' => ['en' => 'Keep English', 'de' => 'Keep German'],
            ],
        ];
    }

    private function create(string $feature, array $data): Model
    {
        $repository = $this->app->make($feature === 'Post' ? PostRepository::class : CategoryRepository::class);

        return $repository->{'create'.$feature.'WithTranslations'}($data);
    }

    private function update(string $feature, Model $parent, array $data): Model
    {
        $repository = $this->app->make($feature === 'Post' ? PostRepository::class : CategoryRepository::class);

        return $repository->{'update'.$feature.'WithTranslations'}($parent, $data);
    }

    private function postUniquenessMigration(): \Illuminate\Database\Migrations\Migration
    {
        DB::connection()->getSchemaBuilder()->table('post_translations', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'language']);
        });

        return require __DIR__.'/../database/migrations/2026_10_25_000002_add_unique_post_language_to_post_translations.php';
    }

    public static function duplicateRows(): array
    {
        return ['active' => [null], 'soft-deleted' => ['2026-10-25 00:00:00']];
    }

    /** @dataProvider duplicateRows */
    public function test_post_uniqueness_migration_refuses_existing_duplicates_without_changing_data(?string $deletedAt): void
    {
        $parent = $this->create('Post', $this->data());
        $migration = $this->postUniquenessMigration();
        $duplicate = (array) DB::table('post_translations')->where('post_id', $parent->id)->where('language', 'en')->first();
        unset($duplicate['id']);
        $duplicate['deleted_at'] = $deletedAt;
        DB::table('post_translations')->insert($duplicate);
        $before = DB::table('post_translations')->orderBy('id')->get()->all();

        try {
            $migration->up();
            self::fail('Existing duplicates must prevent adding the unique constraint.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('post_id '.$parent->id.' and language en', $e->getMessage());
        }
        self::assertEquals($before, DB::table('post_translations')->orderBy('id')->get()->all());
    }

    public function test_post_uniqueness_migration_preserves_valid_data_and_supports_rollback(): void
    {
        $this->create('Post', $this->data());
        $this->create('Post', $this->data());
        $migration = $this->postUniquenessMigration();
        $before = DB::table('post_translations')->orderBy('id')->get()->all();
        $migration->up();
        self::assertEquals($before, DB::table('post_translations')->orderBy('id')->get()->all());

        $duplicate = (array) $before[0];
        unset($duplicate['id']);
        $duplicate['deleted_at'] = '2026-10-25 00:00:00';
        try {
            DB::table('post_translations')->insert($duplicate);
            self::fail('The database must reject duplicate pairs, including soft-deleted rows.');
        } catch (\Illuminate\Database\QueryException $e) {
            self::assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
        }

        $migration->down();
        DB::table('post_translations')->insert($duplicate);
        self::assertSame(5, DB::table('post_translations')->count());
    }

    /** @dataProvider features */
    public function test_updates_reuse_deleted_rows_preserve_omitted_fields_and_clear_explicit_nulls(string $feature): void
    {
        $parent = $this->create($feature, $this->data());
        $english = $parent->translations()->where('language', 'en')->firstOrFail();
        $english->delete();
        $data = $this->data();
        $data['translations'] = [
            'title' => ['en' => 'Updated English', 'de' => 'Updated German', 'fr' => 'Ignored'],
            'excerpt' => ['de' => null],
            'unexpected' => ['en' => 'Ignored'],
        ];
        $this->update($feature, $parent, $data);

        self::assertSame(2, $parent->translations()->withTrashed()->count());
        $restored = $parent->translations()->where('language', 'en')->firstOrFail();
        self::assertSame($english->id, $restored->id);
        self::assertSame('Updated English', $restored->title);
        self::assertSame('Keep English', $restored->excerpt);
        self::assertSame('<p>English</p>', $restored->content);
        self::assertNull($parent->translations()->where('language', 'de')->firstOrFail()->excerpt);
        self::assertSame('Updated German', $parent->translations()->where('language', 'de')->firstOrFail()->title);
    }

    public static function cancellations(): array
    {
        $cases = [];
        foreach (['Post', 'Category'] as $feature) {
            foreach (['create', 'update'] as $operation) {
                foreach (['parent', 'translation'] as $target) {
                    $cases[$feature.' '.$operation.' '.$target] = [$feature, $operation, $target];
                }
            }
        }

        return $cases;
    }

    /** @dataProvider cancellations */
    public function test_cancelled_saves_roll_back_parent_and_all_languages(string $feature, string $operation, string $target): void
    {
        $parent = $operation === 'update' ? $this->create($feature, $this->data()) : null;
        $model = 'Maxi032\\LaravelAdminPackage\\Models\\'.$feature.($target === 'translation' ? 'Translation' : '');
        $model::saving(fn ($record) => $target === 'translation' && $record->language !== 'de' ? null : false);
        $data = $this->data();
        $data['status'] = 0;
        $data['translations']['title'] = ['en' => 'Changed English', 'de' => 'Changed German'];

        try {
            $parent ? $this->update($feature, $parent, $data) : $this->create($feature, $data);
            self::fail('A cancelled save must fail the operation.');
        } catch (RuntimeException $e) {
            self::assertSame('The '.strtolower($feature).' could not be saved. Please try again.', $e->getMessage());
        }
        $parentClass = 'Maxi032\\LaravelAdminPackage\\Models\\'.$feature;
        self::assertSame($parent ? 1 : 0, $parentClass::count());
        if ($parent) {
            self::assertSame(1, $parent->fresh()->status);
            self::assertSame(['Original English', 'Original German'], $parent->translations()->orderBy('id')->pluck('title')->all());
        } else {
            self::assertSame(0, DB::table(strtolower($feature).'_translations')->count());
        }
        self::assertSame(0, DB::transactionLevel());
    }

    /** @dataProvider features */
    public function test_cancelled_restore_rolls_back_saved_translations_and_parent(string $feature): void
    {
        $parent = $this->create($feature, $this->data());
        $german = $parent->translations()->where('language', 'de')->firstOrFail();
        $german->delete();
        $translationClass = get_class($german);
        $translationClass::restoring(fn () => false);
        $data = $this->data();
        $data['status'] = 0;
        $data['translations']['title'] = ['en' => 'Changed English', 'de' => 'Changed German'];

        try {
            $this->update($feature, $parent, $data);
            self::fail('A cancelled restore must fail the operation.');
        } catch (RuntimeException $e) {
            self::assertSame($feature.' translation recovery was cancelled.', $e->getMessage());
        }
        self::assertSame(1, $parent->fresh()->status);
        self::assertSame(['Original English', 'Original German'], $parent->translations()->withTrashed()->orderBy('id')->pluck('title')->all());
        self::assertTrue($german->fresh()->trashed());
        self::assertSame(0, DB::transactionLevel());
    }

    public static function forms(): array
    {
        return [
            'create post' => [PostRequest::class, 'POST'],
            'update post' => [PostRequest::class, 'PUT'],
            'create category' => [CategoryRequest::class, 'POST'],
            'update category' => [CategoryRequest::class, 'PUT'],
        ];
    }

    private function validateForm(string $requestClass, string $method, array $data): array
    {
        $request = $requestClass::create('/', $method, $data);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->validateResolved();

        return $request->validated();
    }

    /** @dataProvider forms */
    public function test_forms_preserve_normalization_and_feature_specific_behavior(string $requestClass, string $method): void
    {
        $this->create('Category', $this->data());
        $data = $this->data();
        $data['sort_order'] = '';
        $data['status'] = $requestClass === CategoryRequest::class ? 'on' : '-1';
        $data['translations']['title']['en'] = '  Title  ';
        $data['translations']['slug']['en'] = '  New Slug  ';
        $data['translations']['excerpt']['en'] = '   ';
        $data['translations']['content']['en'] = '  <p>Keep whitespace</p>  ';
        $validated = $this->validateForm($requestClass, $method, $data);
        self::assertSame('Title', $validated['translations']['title']['en']);
        self::assertSame('new-slug', $validated['translations']['slug']['en']);
        self::assertNull($validated['translations']['excerpt']['en']);
        self::assertSame($data['translations']['content']['en'], $validated['translations']['content']['en']);
        self::assertNull($validated['sort_order']);
        self::assertSame($requestClass === CategoryRequest::class ? true : '-1', $validated['status']);
        $request = $requestClass::create('/');
        self::assertSame($requestClass === CategoryRequest::class ? 'Meta Title' : 'meta_title', $request->attributes()['translations.meta_title.en']);
    }

    /** @dataProvider forms */
    public function test_forms_reject_unknown_fields_languages_missing_content_and_excessive_lengths(string $requestClass, string $method): void
    {
        $this->create('Category', $this->data());
        $invalid = [
            ['unknown', 'en', 'bad', 'translations'],
            ['title', 'fr', 'bad', 'translations.title'],
            ['title', 'en', str_repeat('x', 151), 'translations.title.en'],
            ['slug', 'en', str_repeat('x', 151), 'translations.slug.en'],
            ['excerpt', 'en', str_repeat('x', 10001), 'translations.excerpt.en'],
            ['meta_title', 'en', str_repeat('x', 151), 'translations.meta_title.en'],
            ['meta_keywords', 'en', str_repeat('x', 256), 'translations.meta_keywords.en'],
            ['meta_description', 'en', str_repeat('x', 256), 'translations.meta_description.en'],
            ['content', 'de', null, 'translations.content.de'],
            ['title', 'en', [], 'translations.title.en'],
        ];
        foreach ($invalid as [$field, $language, $value, $errorKey]) {
            $data = $this->data();
            $data['translations'][$field][$language] = $value;
            try {
                $this->validateForm($requestClass, $method, $data);
                self::fail('Invalid translation input must be rejected: '.$errorKey);
            } catch (ValidationException $e) {
                self::assertArrayHasKey($errorKey, $e->errors());
                if ($errorKey === 'translations.title.en') {
                    self::assertStringStartsWith('En: ', $e->errors()[$errorKey][0]);
                }
            }
        }
    }
}
