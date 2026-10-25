<?php

use Maxi032\LaravelAdminPackage\Models\PostType;

it('allows mass assignment of post type fields', function () {
    $type = new PostType([
        'type' => 'products',
        'status' => true,
    ]);

    expect($type->type)->toBe('products')
        ->and($type->status)->toBeTrue();
});
