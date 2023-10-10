<?php

namespace Maxi032\LaravelAdminPackage\Requests;

use \Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
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
            'translations.title.*'   => 'required|max:40',
            'translations.slug.*'    => 'required|max:50',
            'translations.content.*' => 'required',
            //'type_id' => 'required|exists:Maxi032\LaravelAdminPackage\Models\PostType,id'
            'type_id' => 'required',
            'category_id' => 'required'
        ];
    }

    public function updateRules(): array
    {
        return [
            'translations.title.*'   => 'required|max:50',
            'translations.slug.*'    => 'required|max:50',
            'translations.content.*' => 'required',
            //'type_id' => 'required|exists:Maxi032\LaravelAdminPackage\Models\PostType,id'
            'type_id' => 'required',
            'category_id' => 'required'
        ];
    }

    public function messages(): array
    {
       $messages = [];
       $languages = getLanguages();
       foreach($this->rules() as $fields=>$rules){
          if(Str::startsWith($fields,'translations.')){
           $fieldRules = collect(explode('|',$rules));
           $field = substr($fields,13, -2);
              $fieldRules->each(function ($ruleItem) use ($field, $languages, &$messages) {
                  $ruleName = ($pos = strpos($ruleItem, ':')) !== false ? substr($ruleItem, 0, $pos) : $ruleItem;
                  $criteriaValue = ($pos = strpos($ruleItem, ':')) !== false ? substr($ruleItem, $pos+1, strlen($ruleItem)) : '';
                  foreach($languages as $languageKey => $language) {
                      if(is_array(trans('validation.'.$ruleName))){
                          foreach(trans('validation.'.$ruleName) as $arrayRuleKey => $arrk) {
                              $messages['translations.' . $field . '.' . $language['code'] . '.'.$ruleName] = match ($arrayRuleKey) {
                                  'array' => (Lang::has('validation.'.$ruleName.'.array')) ? ucfirst($language['code']) . ': ' . Lang::get('validation.' . $ruleName.'.array', ['attribute' => $field, $ruleName => $criteriaValue]):null,
                                  'file' => (Lang::has('validation.'.$ruleName.'.file')) ? ucfirst($language['code']) . ': ' . Lang::get('validation.' . $ruleName.'.filer', ['attribute' => $field, $ruleName => $criteriaValue]):null,
                                  'numeric' => (Lang::has('validation.'.$ruleName.'.numeric')) ? ucfirst($language['code']) . ': ' . Lang::get('validation.' . $ruleName.'.numeric', ['attribute' => $field, $ruleName => $criteriaValue]):null,
                                  'string' => (Lang::has('validation.'.$ruleName.'.string')) ? ucfirst($language['code']) . ': ' . Lang::get('validation.' . $ruleName.'.string', ['attribute' => $field, $ruleName => $criteriaValue]):null,
                              };
                          }
                      } else {
                          $messages['translations.' . $field . '.' . $language['code'] . '.' . $ruleName] = ucfirst($language['code']) . ': ' . trans('validation.' . $ruleName, ['attribute' => $field, $ruleName => $criteriaValue]);
                      }
                  }

                  return $messages;
              });
          }
       }

       return $messages;
    }

    public function attributes()
    {
        $attributes = [];
       foreach($this->rules() as $fields=>$rules){
           if(Str::startsWith($fields,'translations.')) {
               $field = substr($fields,13, -2);
               foreach (getLanguages() as $languageKey => $language ) {
                   $attributes[$field][$language['code']] = trans($field);
               }
           } else{
               $attributes[$field] = trans($field);
           }
       }
        return $attributes;
    }

}
