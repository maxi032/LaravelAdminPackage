<?php

namespace Maxi032\LaravelAdminPackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryMatchesPostType implements Rule
{
    public function __construct(
        private readonly mixed $postTypeId,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function passes($attribute, $value): bool
    {
        foreach ([$this->postTypeId, $value] as $id) {
            if ((!is_int($id) && !is_string($id)) || filter_var($id, FILTER_VALIDATE_INT) === false) {
                return false;
            }
        }

        return $this->categoryRepository->categoryMatchesPostType((int) $value, (int) $this->postTypeId);
    }

    public function message(): string
    {
        return __('The selected category does not belong to the selected post type.');
    }
}
