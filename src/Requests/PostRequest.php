<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    private const TRANSLATION_FIELDS = [
        'title', 'slug', 'content', 'excerpt',
        'meta_title', 'meta_keywords', 'meta_description',
    ];

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
            foreach (['title', 'slug', 'excerpt', 'meta_title', 'meta_keywords', 'meta_description'] as $field) {
                if (!isset($translations[$field]) || !is_array($translations[$field])) {
                    continue;
                }

                foreach ($translations[$field] as $language => $value) {
                    if (!is_string($value)) {
                        continue;
                    }

                    $value = trim($value);
                    $translations[$field][$language] = $field === 'slug'
                        ? Str::slug($value)
                        : ($value === '' ? null : $value);
                }
            }
            $data['translations'] = $translations;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return $this->isMethod('post') ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return $this->postRules(50);
    }

    public function updateRules(): array
    {
        return $this->postRules(50);
    }

    private function postRules(int $titleLength): array
    {
        $languages = array_column(config('laravel-admin-package.allowed_languages', []), 'code');
        $rules = [
            'type_id' => ['required', 'integer', Rule::exists('post_types', 'id')->whereNull('deleted_at')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'status' => ['required', 'boolean'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:4294967295'],
            'translations' => ['required', 'array:'.implode(',', self::TRANSLATION_FIELDS)],
        ];

        foreach (self::TRANSLATION_FIELDS as $field) {
            $rules['translations.'.$field] = [
                in_array($field, ['title', 'slug', 'content'], true) ? 'required' : 'sometimes',
                'array:'.implode(',', $languages),
            ];
        }

        foreach ($languages as $language) {
            $rules['translations.title.'.$language] = ['required', 'string', 'max:'.$titleLength];
            $rules['translations.slug.'.$language] = ['required', 'string', 'max:50'];
            $rules['translations.content.'.$language] = ['required', 'string'];
            $rules['translations.excerpt.'.$language] = ['nullable', 'string', 'max:10000'];
            $rules['translations.meta_title.'.$language] = ['nullable', 'string', 'max:150'];
            $rules['translations.meta_keywords.'.$language] = ['nullable', 'string', 'max:255'];
            $rules['translations.meta_description.'.$language] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [];
        foreach (array_column(config('laravel-admin-package.allowed_languages', []), 'code') as $language) {
            foreach (self::TRANSLATION_FIELDS as $field) {
                foreach (['required', 'string', 'max'] as $rule) {
                    $key = $rule === 'max' ? 'validation.max.string' : 'validation.'.$rule;
                    $messages['translations.'.$field.'.'.$language.'.'.$rule] = ucfirst($language).': '.Lang::get($key);
                }
            }
        }

        return $messages;
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach (['type_id', 'category_id', 'status', 'sort_order', 'translations'] as $field) {
            $attributes[$field] = __($field);
        }
        foreach (self::TRANSLATION_FIELDS as $field) {
            $attributes['translations.'.$field] = __($field);
            foreach (array_column(config('laravel-admin-package.allowed_languages', []), 'code') as $language) {
                $attributes['translations.'.$field.'.'.$language] = __($field);
            }
        }

        return $attributes;
    }
}
