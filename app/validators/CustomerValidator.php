<?php

namespace App\validators;
use App\util\BaseValidator;

class CustomerValidator extends BaseValidator
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'customer';
    }

    protected function rules()
    {
        // TODO: Implement rules() method.
        return[
            'add'=>[
                'first_name'=>['required','string'],
                'last_name'=>['required','string'],
                'middle_name'=>['required','string'],
                'email'=>['bail','required','email'],
                'gender' => ['required', 'string'],
                'company_id' => ['required', 'string']
            ],

            'update' => [
                'first_name'=>['sometimes','string'],
                'last_name'=>['sometimes','string'],
                'middle_name'=>['sometimes','string'],
                'email'=>['sometimes','email'],
                'gender' => ['sometimes', 'string'],
                'company_id' => ['sometimes', 'string']
            ]
        ];
    }
}