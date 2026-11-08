<?php

namespace Maxi032\LaravelAdminPackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ValidPostOptionValues implements Rule
{
    private array $errors = [];

    public function __construct(private readonly Collection $definitions)
    {
    }

    public function passes($attribute, $value): bool
    {
        $validator = Validator::make(
            [$attribute => $value],
            $this->rules($attribute),
            [],
            $this->attributes($attribute),
        );
        $this->errors = $validator->errors()->getMessages();

        return $this->errors === [];
    }

    public function message(): array
    {
        return array_map(static fn (array $messages): string => $messages[0], $this->errors);
    }

    /** Revalidate submitted values against the definitions locked for the save. */
    public function validateInput(array $input): void
    {
        if (!$this->passes('options', $input)) {
            throw ValidationException::withMessages($this->errors);
        }
    }

    private function rules(string $attribute): array
    {
        $languages = array_column(config('laravel-admin-package.allowed_languages', []), 'code');
        $rules = [
            $attribute => self::allowedKeys($this->definitions->pluck('key')->all()),
        ];

        foreach ($this->definitions as $option) {
            $path = $attribute.'.'.$option->key;
            $valueRules = match ($option->type) {
                'boolean' => ['boolean'],
                'integer' => ['nullable', 'integer', $option->key === 'reading_time' ? 'min:1' : 'min:-2147483648', 'max:2147483647'],
                'text' => ['nullable', 'string', 'max:10000'],
            };

            if ($option->is_translatable) {
                $rules[$path] = self::allowedKeys($languages);
                foreach ($languages as $language) {
                    $rules[$path.'.'.$language] = $valueRules;
                }
            } else {
                $rules[$path] = $valueRules;
            }
        }

        return $rules;
    }

    private static function allowedKeys(array $keys): array
    {
        return $keys === [] ? ['array', 'max:0'] : ['array:'.implode(',', $keys)];
    }

    private function attributes(string $attribute): array
    {
        $attributes = [];
        foreach ($this->definitions as $option) {
            $label = $option->label;
            $path = $attribute.'.'.$option->key;
            $attributes[$path] = $label;
            if ($option->is_translatable) {
                foreach (config('laravel-admin-package.allowed_languages', []) as $language) {
                    $attributes[$path.'.'.$language['code']] = $label.' ('.$language['code'].')';
                }
            }
        }

        return $attributes;
    }
}
