<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maxi032\LaravelAdminPackage\Helpers\TranslationInputHelper;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $status = match ($this->input('status', false)) {
            true, 1, '1', 'on' => true,
            false, 0, '0' => false,
            default => $this->input('status'),
        };

        $data = ['status' => $status];
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
        return $this->categoryRules();
    }

    public function updateRules(): array
    {
        return $this->categoryRules();
    }

    private function categoryRules(): array
    {
        $languages = array_column(config('laravel-admin-package.allowed_languages', []), 'code');
        $typeRule = Rule::exists('category_types', 'id')->whereNull('deleted_at');
        $rules = [
            'type_id' => ['required', 'integer', $typeRule],
            'status' => ['required', 'boolean'],
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
        foreach (['type_id' => 'Type', 'status' => 'Status', 'sort_order' => 'Position', 'translations' => 'Content'] as $field => $label) {
            $attributes[$field] = __($label);
        }
        $labels = ['meta_title' => 'Meta Title', 'meta_keywords' => 'Meta keywords', 'meta_description' => 'Meta description'];
        foreach (TranslationInputHelper::FIELDS as $field) {
            $attributes['translations.'.$field] = __($labels[$field] ?? ucfirst($field));
            foreach (array_column(config('laravel-admin-package.allowed_languages', []), 'code') as $language) {
                $attributes['translations.'.$field.'.'.$language] = __($labels[$field] ?? ucfirst($field));
            }
        }

        return $attributes;
    }
}
