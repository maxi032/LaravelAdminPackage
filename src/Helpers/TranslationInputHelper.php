<?php

namespace Maxi032\LaravelAdminPackage\Helpers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * This is used in both Post and Category translations
 * */
final class TranslationInputHelper
{
    public const FIELDS = [
        'title', 'slug', 'content', 'excerpt',
        'meta_title', 'meta_keywords', 'meta_description',
    ];

    /**
     * Trim text, generate slugs, and convert blank text to null while preserving content.
     */
    public static function normalize(array $translations): array
    {
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

        return $translations;
    }

    /**
     * Build translation validation rules for the allowed fields and language codes.
     */
    public static function rules(array $languages): array
    {
        $rules = [
            'translations' => ['required', 'array:'.implode(',', self::FIELDS)],
        ];

        foreach (self::FIELDS as $field) {
            $rules['translations.'.$field] = [
                in_array($field, ['title', 'slug', 'content'], true) ? 'required' : 'sometimes',
                'array:'.implode(',', $languages),
            ];
        }

        foreach ($languages as $language) {
            $rules['translations.title.'.$language] = ['required', 'string', 'max:150'];
            $rules['translations.slug.'.$language] = ['required', 'string', 'max:150'];
            $rules['translations.content.'.$language] = ['required', 'string'];
            $rules['translations.excerpt.'.$language] = ['nullable', 'string', 'max:10000'];
            $rules['translations.meta_title.'.$language] = ['nullable', 'string', 'max:150'];
            $rules['translations.meta_keywords.'.$language] = ['nullable', 'string', 'max:255'];
            $rules['translations.meta_description.'.$language] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Build localized validation messages prefixed with the affected language code.
     */
    public static function messages(array $languages): array
    {
        $messages = [];
        foreach ($languages as $language) {
            foreach (self::FIELDS as $field) {
                foreach (['required', 'string', 'max'] as $rule) {
                    $key = $rule === 'max' ? 'validation.max.string' : 'validation.'.$rule;
                    $messages['translations.'.$field.'.'.$language.'.'.$rule] = ucfirst($language).': '.Lang::get($key);
                }
            }
        }

        return $messages;
    }
}
