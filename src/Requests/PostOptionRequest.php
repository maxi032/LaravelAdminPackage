<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maxi032\LaravelAdminPackage\Models\PostOption;

class PostOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('post_types_present') && !$this->has('post_type_ids')) {
            $this->merge(['post_type_ids' => []]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:150'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'boolean_default' => ['sometimes', 'boolean'],
            'visible_in_post' => ['sometimes', 'boolean'],
            'is_eager_loaded' => ['sometimes', 'boolean'],
            'post_type_ids' => [$this->route('option') ? 'sometimes' : 'required', 'array'],
            'post_type_ids.*' => ['bail', 'required', 'integer', 'distinct', Rule::exists('post_types', 'id')->whereNull('deleted_at')],
        ];

        $option = $this->route('option');
        if ($option === null) {
            $rules['key'] = ['required', 'string', 'max:64', 'regex:/\A[a-z][a-z0-9_]*\z/', Rule::unique('post_option', 'key')];
        }

        $presence = $option ? 'sometimes' : 'required';
        $rules['enabled'] = [$presence, 'boolean'];
        $rules['type'] = [$presence, Rule::in(PostOption::TYPES)];
        $rules['is_translatable'] = [$presence, 'boolean'];

        return $rules;
    }

    public function messages(): array
    {
        return ['post_type_ids.required' => __('Select at least one post type.')];
    }
}
