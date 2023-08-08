<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        if( $this->isMethod('post') ) {
            return $this->createRules();
        } elseif ( $this->isMethod('put') ) {
            return $this->updateRules();
        }
    }

    public function createRules(){

    }

    public function updateRules(){

    }

    public function messages(): array
    {
        return [
            //
        ];
    }
}
