# LaravelAdminPackage
WIP Laravel admin package

### This is meant as a demo. It follows as much as possible the database structure of Wordpress
## Configuration

Package defaults are loaded automatically. Publishing configuration is optional:

```sh
php artisan vendor:publish --tag=maxi032-laravel-admin-package-config
```

Override individual settings in `config/laravel-admin-package.php`:

```php
return [
    'admin_url' => 'backoffice',
    'package_views_first' => false,
];
```

`admin_url` defaults to `admin`. Changing it to `backoffice` changes the URL prefix
from `/admin` to `/backoffice` and route names from `admin:*` to `backoffice:*`.
Package routes, redirects, and templates all use the same prefix. Package views
receive `$adminRoutePrefix`; customized templates should use
`route($adminRoutePrefix.'posts.create')` instead of hardcoding `admin:`.

Omitted settings retain their package defaults. An explicitly supplied
`allowed_languages` array replaces the complete default list; it is not merged
by numeric index.

## View overrides

Publish templates when you need to customize them:

```sh
php artisan vendor:publish --tag=maxi032-laravel-admin-package-views
```

With `package_views_first` set to `false` (the default), views are resolved in order:

1. `resources/views/vendor/maxi032/laravel-admin-package`.
2. Laravel's conventional `vendor/laravel-admin-package` directories within the configured view paths.
3. The package's `resources/views` directory.

With `package_views_first` set to `true`, package templates take precedence;
published templates remain fallbacks for views that the package does not provide.

For compatibility, the setting defaults to the existing environment variable
`MAXI032_LAP_LOAD_VIEWS_FROM_VENDOR_FIRST`. Despite that variable's legacy name,
`true` means **package views first**, preserving its previous behavior. An explicit
`package_views_first` configuration value takes precedence. Environment access
occurs only in the configuration file, so the setting works with cached config.

Existing published templates are not overwritten on package updates. Update any
hardcoded route prefixes in your customized copies, or republish the views after
preserving your customizations.

## Configuration and route caches

After changing configuration or the legacy environment variable, rebuild any
configuration, route, and view caches used by the application:

```sh
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

## Deleting posts

The authenticated resource route `DELETE /admin/cms/posts/{post}` deletes a post
by its numeric ID (`admin:posts.destroy`). The URL and route-name prefixes follow
`admin_url`. The post list's Delete button opens a translated confirmation modal.
Cancel dismisses the modal; only Confirm delete submits its CSRF-protected form.
The action then redirects to the post's type list with a success or failure message. Missing or
already deleted IDs return 404 through route model binding.

Deletion soft deletes the post and its active translations together in a database
transaction. Rows remain in the database with `deleted_at` set; normal queries
exclude them. Other posts, including child posts, are unaffected. A failure rolls
back the whole deletion.

Both models already use `SoftDeletes`, so Eloquent `destroy($id)` also performs a
soft deletion, despite its name.

The Trash button immediately to the left of Add opens
`GET /admin/cms/posts/trash/{type}` (`admin:posts.trash`). This overview shows only
deleted posts of that type, including deleted translations. Each row has Recover
and Destroy actions:

- `POST /admin/cms/posts/{post}/recover` (`admin:posts.recover`) restores the post
  and its deleted translations in one transaction.
- `DELETE /admin/cms/posts/{post}/force-delete` (`admin:posts.force_delete`)
  permanently deletes the post and all its translations. Database foreign-key
  cascades also remove child posts and their translations. A translated modal
  explains this irreversible action; only Confirm destroy submits the form.

Both actions require authentication, CSRF protection, and a deleted post ID;
active or missing IDs return 404. Success or failure redirects to the trash
overview with a translated toast. Failures roll back the transaction. The
overview, dialogs, messages, and custom exceptions support English, Romanian,
Dutch, and French. All URLs and route names follow the configured `admin_url`.

## Verification

Tests run from the host Laravel application using Pest 2 and PHPUnit 10. Pest
runs the existing PHPUnit test classes alongside functional Pest tests. Run all
application and package tests, or just the package suite:

```sh
composer test
composer test:package
```

With the project's running Sail container:

```sh
docker compose exec -T laravel.test composer test
docker compose exec -T laravel.test composer test:package
```

The host's `tests/Pest.php` applies `Tests\TestCase` only to application feature
tests. Package tests retain their own isolated application bootstrap; database
tests create in-memory SQLite schemas and do not use the application's database.
The package suite covers configuration and route caches, model filtering,
translation constraints, post persistence, deletion, translated toasts, and
confirmation modals. PHP's PDO SQLite extension is required for database tests.

Run just the configuration integration tests with:

```sh
vendor/bin/pest --filter PackageConfigurationTest
```

These tests do not require a database. They cover unpublished defaults, partial
overrides, language-list replacement, custom route prefixes in links/forms/AJAX
and redirects, view precedence and fallbacks, and cached configuration/admin routes.
The host application's PHPUnit configuration also discovers the complete package
suite, whose persistence tests require the PDO SQLite extension.
