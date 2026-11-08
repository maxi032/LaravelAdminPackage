<?php

namespace Maxi032\LaravelAdminPackage\Services;

use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostOptionRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\PostOptionRepository;
use Maxi032\LaravelAdminPackage\Rules\ValidPostOptionValues;

final class PostOptionValues
{
    public function __construct(
        private readonly PostOptionRepositoryInterface $postOptionRepository = new PostOptionRepository(),
    ) {
    }

    /** Synchronize submitted fields inside the post's transaction. */
    public function sync(Post $post, array $input): void
    {
        if ($input === []) {
            return;
        }

        $definitions = $this->postOptionRepository->lockEditablePostOptionsByKey(array_keys($input));

        $blankToNull = static fn (mixed $value) => is_string($value) && trim($value) === '' ? null : $value;
        $input = array_map(
            fn (mixed $value) => is_array($value) ? array_map($blankToNull, $value) : $blankToNull($value),
            $input,
        );

        (new ValidPostOptionValues($definitions))->validateInput($input);

        foreach ($input as $key => $value) {
            $definition = $definitions[$key];
            foreach ($definition->is_translatable ? $value : ['' => $value] as $locale => $localizedValue) {
                $this->saveValue($post, $definition, $locale, $localizedValue);
            }
        }

        $post->unsetRelation('optionValues');
    }

    private function saveValue(Post $post, PostOption $definition, string $locale, mixed $value): void
    {
        if ($value === null) {
            $this->postOptionRepository->deletePostOptionValue($post, $definition->id, $locale);

            return;
        }

        $this->postOptionRepository->savePostOptionValue($post, $definition, $locale, $value);
    }
}
