<?php

namespace Maxi032\LaravelAdminPackage\Helpers;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use RuntimeException;

final class TranslationSynchronizerHelper
{
    /** Run inside the parent's transaction so any cancelled operation rolls back all changes. */
    public function sync(
        HasMany $translations,
        array $input,
        array $languages,
        string $saveFailureMessage,
        string $restoreFailureMessage,
    ): void {
        $input = Arr::only($input, TranslationInputHelper::FIELDS);

        foreach ($languages as $language) {
            $attributes = [];
            foreach ($input as $field => $values) {
                // Omitted fields are preserved; an explicit null clears the value.
                if (array_key_exists($language, $values)) {
                    $attributes[$field] = $values[$language];
                }
            }

            // Clone the relationship so each language has an independent query.
            $translation = (clone $translations)->withTrashed()->firstOrNew(['language' => $language]);
            if ($translation->fill($attributes)->save() === false) {
                throw new RuntimeException($saveFailureMessage);
            }
            if ($translation->trashed() && $translation->restore() === false) {
                throw new RuntimeException($restoreFailureMessage);
            }
        }
    }
}
