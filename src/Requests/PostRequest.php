<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maxi032\LaravelAdminPackage\Enums\PostStatusEnum;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;
use Maxi032\LaravelAdminPackage\Rules\CategoryMatchesPostType;
use Maxi032\LaravelAdminPackage\Helpers\TranslationInputHelper;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->input('sort_order') === '') {
            $data['sort_order'] = null;
        }

        $translations = $this->input('translations');
        if (is_array($translations)) {
            $data['translations'] = TranslationInputHelper::normalize($translations);
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return $this->isMethod('post') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return $this->postRules();
    }

    public function updateRules(): array
    {
        return $this->postRules();
    }

    private function postRules(): array
    {
        $languages = array_column(config('laravel-admin-package.allowed_languages', []), 'code');
        $rules = [
            'type_id' => ['required', 'integer', Rule::exists('post_types', 'id')->whereNull('deleted_at')],
            'category_id' => ['bail', 'required', 'integer', new CategoryMatchesPostType(
                $this->input('type_id'), app(CategoryRepositoryInterface::class)
            )],
            'status' => ['bail', 'required', 'integer', Rule::enum(PostStatusEnum::class)],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:4294967295'],
        ];

        return array_merge($rules, TranslationInputHelper::rules($languages));
    }

    public function messages(): array
    {
        return TranslationInputHelper::messages(
            array_column(config('laravel-admin-package.allowed_languages', []), 'code')
        );
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach (['type_id', 'category_id', 'status', 'sort_order', 'translations'] as $field) {
            $attributes[$field] = __($field);
        }
        foreach (TranslationInputHelper::FIELDS as $field) {
            $attributes['translations.'.$field] = __($field);
            foreach (array_column(config('laravel-admin-package.allowed_languages', []), 'code') as $language) {
                $attributes['translations.'.$field.'.'.$language] = __($field);
            }
        }

        return $attributes;
    }
}
