<?php

use Illuminate\Support\Str;

if (! function_exists('assets')) {
    /**
     * Generate a relative asset path for the application.
     *
     * @param string $path
     * @return string
     */
    function assets(string $path): string
    {
        return $path;
    }
}

if (! function_exists('trimValidationMessage')) {
    /**
     * Show a clean validation message without language code.
     *
     * @param string $message
     * @return string
     */
    function trimValidationMessage(string $message): string
    {
        return Str::substr($message, 4);
    }

    if (! function_exists('getLanguages')) {
        function getLanguages()
        {
            return config('laravel-admin-package.allowed_languages');
        }
    }
}