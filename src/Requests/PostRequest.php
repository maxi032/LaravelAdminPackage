<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use \Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\NoReturn;
use Request;
use Symfony\Component\HttpFoundation\Response;

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
     * Get the validation rules that apply to the request, based on method.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return ($this->isMethod('post')) ? $this->createRules() : $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'translations.title.*'   => 'required|max:50',
            'translations.slug.*'    => 'required|max:50',
            'translations.content.*' => 'required',
            //'type_id' => 'required|exists:Maxi032\LaravelAdminPackage\Models\PostType,id'
            'type_id' => 'required'
        ];
    }

    public function updateRules(): array
    {
        return [
            'translations.title.*'   => 'required|max:50',
            'translations.slug.*'    => 'required|max:50',
            'translations.content.*' => 'required',
            //'type_id' => 'required|exists:Maxi032\LaravelAdminPackage\Models\PostType,id'
            'type_id' => 'required'
        ];
    }

    public function messages(): array
    {
       $messages = [];
       $languages = config('laravel-admin-package.allowed_languages');
       foreach($this->rules() as $fields=>$rules){
          if(Str::startsWith($fields,'translations.')){
           $fieldRules = collect(explode('|',$rules));
           $field = substr($fields,13, -2);
              $fieldRules->each(function ($ruleItem) use ($field, $languages, &$messages) {
                  foreach($languages as $languageKey => $language) {
                      switch($ruleItem) {
                          case 'required':
                            $messages['translations.' . $field . '.' . $language['code'] . '.' . $ruleItem] = ucfirst($language['code']).': '.trans('validation.required', ['attribute' => $field]);
                          break;
                      }
                  }
                  return $messages;
              });
          }
       }
       return $messages;
    }

}
