<?php

namespace Maxi032\LaravelAdminPackage\Tests;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Http\Controllers\PostController;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\RepositoryServiceProvider;
use Maxi032\LaravelAdminPackage\Requests\PostStatusRequest;
use Maxi032\LaravelAdminPackage\Services\PostService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PostStatusTest extends TestCase
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
        DB::connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Post::flushEventListeners();
        $this->app['db']->disconnect();
        Model::unsetConnectionResolver();
        Model::clearBootedModels();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        $this->app->flush();
        parent::tearDown();
    }

    private function statusRequest(array $data): PostStatusRequest
    {
        $request = PostStatusRequest::create('/admin/cms/posts/change_status', 'POST', $data);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->validateResolved();

        return $request;
    }

    public function test_status_changes_persist_only_the_target_post_and_keep_the_json_contract(): void
    {
        $target = Post::create(['status' => false]);
        $other = Post::create(['status' => true]);
        $controller = $this->app->make(PostController::class);

        foreach (['1' => 'Post was activated', '0' => 'Post was deactivated'] as $status => $message) {
            $request = $this->statusRequest([
                'id' => (string) $target->id, 'status' => (string) $status,
                'unvalidated' => 'ignored',
            ]);
            self::assertArrayNotHasKey('unvalidated', $request->validated());
            $response = $controller->ajaxChangeStatus($request);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame(['success' => $message], $response->getData(true));
            self::assertSame((int) $status, $target->fresh()->status);
            self::assertSame(1, $other->fresh()->status);
        }
    }

    /** @dataProvider invalidInputs */
    public function test_external_request_rejects_invalid_input(array $data, string $field): void
    {
        try {
            $this->statusRequest($data);
            self::fail('Expected invalid status input to fail validation.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());
            self::assertSame(422, $e->status);
        }
        self::assertSame(0, Post::count());
    }

    public static function invalidInputs(): array
    {
        return [
            'missing id' => [['status' => '1'], 'id'],
            'invalid id' => [['id' => 'invalid', 'status' => '1'], 'id'],
            'missing status' => [['id' => 1], 'status'],
            'invalid status' => [['id' => 1, 'status' => 'invalid'], 'status'],
            'non-boolean status' => [['id' => 1, 'status' => 2], 'status'],
            'array status' => [['id' => 1, 'status' => []], 'status'],
        ];
    }

    public function test_missing_and_soft_deleted_posts_still_raise_not_found(): void
    {
        $deleted = Post::create(['status' => true]);
        $deleted->delete();

        foreach ([$deleted->id, 999] as $id) {
            try {
                $this->app->make(PostController::class)->ajaxChangeStatus(
                    $this->statusRequest(['id' => $id, 'status' => false])
                );
                self::fail('Expected missing or deleted posts to remain inaccessible.');
            } catch (ModelNotFoundException $e) {
                self::assertSame(Post::class, $e->getModel());
            }
        }
        self::assertSame(1, Post::onlyTrashed()->findOrFail($deleted->id)->status);
    }

    public function test_cancelled_saves_roll_back_and_return_an_error_instead_of_success(): void
    {
        $post = Post::create(['status' => true]);
        Post::saving(fn () => false);

        try {
            $this->app->make(PostService::class)->changePostStatus($post->id, false);
            self::fail('Expected cancelled persistence to propagate.');
        } catch (RuntimeException $e) {
            self::assertSame('The status could not be updated. Please try again.', $e->getMessage());
        }

        $response = $this->app->make(PostController::class)->ajaxChangeStatus(
            $this->statusRequest(['id' => $post->id, 'status' => '0'])
        );
        self::assertSame(500, $response->getStatusCode());
        self::assertSame(['error' => 'The status could not be updated. Please try again.'], $response->getData(true));
        self::assertSame(1, $post->fresh()->status);
        self::assertSame(0, DB::transactionLevel());
    }

    public function test_controller_uses_service_and_hides_internal_error_details(): void
    {
        $service = $this->createMock(PostService::class);
        $service->expects(self::once())->method('changePostStatus')->with(7, false)
            ->willThrowException(new RuntimeException('Internal failure details'));

        $response = (new PostController($service))->ajaxChangeStatus(
            $this->statusRequest(['id' => '7', 'status' => '0'])
        );
        self::assertSame(500, $response->getStatusCode());
        self::assertSame(['error' => 'The status could not be updated. Please try again.'], $response->getData(true));
    }

    public function test_service_delegates_status_changes_to_repository(): void
    {
        $repository = $this->createMock(PostRepositoryInterface::class);
        $repository->expects(self::once())->method('changePostStatus')->with(7, true);
        $service = new PostService(
            $repository,
            $this->createMock(PostTypeRepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class),
        );

        $service->changePostStatus(7, true);
    }
    public function test_all_status_values_are_validated_and_preserved_by_post_forms(): void
    {
        foreach ([-1, 0, 1, 2, 3] as $status) {
            foreach (['POST', 'PUT'] as $method) {
                $request = \Maxi032\LaravelAdminPackage\Requests\PostRequest::create('/', $method, ['status' => (string) $status]);
                $rules = $request->rules()['status'];
                $validated = $this->app['validator']->make($request->all(), ['status' => $rules])->validate();
                self::assertSame((string) $status, $validated['status']);
            }
            $post = Post::create(['status' => $status]);
            self::assertSame($status, $post->fresh()->status);
        }
    }

    public function test_post_forms_reject_missing_or_unknown_status_values(): void
    {
        $request = \Maxi032\LaravelAdminPackage\Requests\PostRequest::create('/');
        $rules = ['status' => $request->rules()['status']];
        foreach ([[], ['status' => 4], ['status' => -2], ['status' => 'on'], ['status' => 'invalid'], ['status' => []], ['status' => '1.5']] as $data) {
            self::assertTrue($this->app['validator']->make($data, $rules)->fails());
        }
    }

    public function test_overview_can_activate_every_non_active_status_and_deactivate_active(): void
    {
        $controller = $this->app->make(PostController::class);
        foreach ([-1, 0, 2, 3] as $status) {
            $post = Post::create(['status' => $status]);
            $response = $controller->ajaxChangeStatus($this->statusRequest(['id' => $post->id, 'status' => 1]));
            self::assertSame(200, $response->getStatusCode());
            self::assertSame(1, $post->fresh()->status);
            $controller->ajaxChangeStatus($this->statusRequest(['id' => $post->id, 'status' => 0]));
            self::assertSame(0, $post->fresh()->status);
        }
    }

    public function test_status_migration_preserves_values_and_guards_rollback(): void
    {
        Post::create(['status' => 0]);
        Post::create(['status' => 1]);
        $migration = require __DIR__.'/../database/migrations/2026_10_25_000001_expand_post_status_values.php';
        $migration->up();
        self::assertSame([0, 1], Post::orderBy('id')->pluck('status')->all());
        $archived = Post::create(['status' => -1]);
        self::assertSame(-1, $archived->fresh()->status);
        try {
            $migration->down();
            self::fail('Rollback must preserve extended statuses.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('before rolling back', $e->getMessage());
        }
        $archived->update(['status' => 0]);
        $migration->down();
        self::assertSame([0, 1, 0], Post::orderBy('id')->pluck('status')->all());
    }

    public function test_create_and_edit_dropdowns_offer_every_status_and_keep_old_input(): void
    {
        $session = new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(120));
        $this->app->instance('session', $session);
        $this->app['request']->setLaravelSession($session);
        $this->app['view']->addLocation(__DIR__.'/../resources/views');
        $this->app['view']->addNamespace('laravel-admin-package', __DIR__.'/../resources/views');
        $this->app['view']->share('laravelAdminPackage', 'laravel-admin-package');
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
        foreach ([null, -1, 0, 1, 2, 3] as $status) {
            $post = $status === null ? null : new Post(['status' => $status]);
            $html = $this->app['view']->make('cms.posts.tabs.main', [
                'tab' => 'main', 'post' => $post, 'postTypes' => [], 'categories' => [],
            ])->render();
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            self::assertSame(6, $xpath->query('//select[@name="status"]/option')->length);
            self::assertSame((string) ($status ?? ''), $xpath->query('//select[@name="status"]/option[@selected]')->item(0)->getAttribute('value'));
        }
        $session->put('_old_input', ['status' => '-1']);
        $html = $this->app['view']->make('cms.posts.tabs.main', [
            'tab' => 'main', 'post' => new Post(['status' => 1]), 'postTypes' => [], 'categories' => [],
        ])->render();
        self::assertMatchesRegularExpression('/value="-1" selected/', $html);
    }

    public function test_overview_shows_labeled_switches_for_all_statuses_with_only_active_checked(): void
    {
        $this->app['view']->addLocation(__DIR__.'/../resources/views');
        foreach (['edit', 'destroy'] as $action) {
            $this->app['router']->get('/posts/{post}/'.$action, fn () => '')->name('test.posts.'.$action);
        }
        $this->app['router']->post('/posts/status', fn () => '')->name('test.posts.ajax_change_status');
        $this->app['router']->getRoutes()->refreshNameLookups();
        $posts = collect([-1, 0, 1, 2, 3])->map(function ($status, $index) {
            $post = new Post(['status' => $status]);
            $post->id = $index + 1;
            $post->setRelation('translations', collect());
            return $post;
        });
        $html = $this->app['view']->make('cms.posts.partials.index_table', [
            'posts' => new \Illuminate\Pagination\LengthAwarePaginator($posts, 5, 15),
            'postType' => (object) ['type' => 'posts'], 'adminRoutePrefix' => 'test.',
        ])->render();
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        self::assertSame(5, $xpath->query('//input[@data-status-toggle]')->length);
        self::assertSame(1, $xpath->query('//input[@data-status-toggle and @checked]')->length);
        self::assertSame('1', $xpath->query('//input[@data-status-toggle and @checked]')->item(0)->getAttribute('data-post-status'));
        foreach (['Pending', 'Active', 'Inactive', 'Draft', 'Archived'] as $label) {
            self::assertStringContainsString('>'.$label.'</label>', $html);
        }
    }

}
