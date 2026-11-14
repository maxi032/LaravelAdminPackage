<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maxi032\LaravelAdminPackage\Models\PostType;

class PostTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type');
        if (is_string($type)) {
            $this->merge(['type' => str_replace('-', '_', $type)]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => [
                'bail', 'required', 'string', 'max:50', 'regex:/\A[a-z][a-z0-9_]*\z/',
                // The key also appears in the content routes, where create is reserved.
                'not_in:create',
                Rule::unique('post_types', 'type')->ignore($this->route('postType')?->getKey()),
            ],
            'status' => ['required', 'boolean'],
            'icon' => ['sometimes', 'required', 'string', Rule::in(array_keys(PostType::iconOptions()))],
        ];
    }

    public function attributes(): array
    {
        return ['type' => __('Type'), 'status' => __('Status'), 'icon' => __('Icon')];
    }
}
