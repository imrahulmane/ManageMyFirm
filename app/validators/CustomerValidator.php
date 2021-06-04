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
                'first_name'=>['required','string']
//                'last_name'=>['required','string'],
//                'middle_name'=>['sometimes','string'],
//                'email'=>['required','array','min:1','max:2'],
//                'email.*.type'=>['required','in:home,office','distinct'],
//                'email.*.value'=>['required','email','distinct'],
//                'phone'=>['sometimes','array','min:1','max:3'],
//                'phone.*.type'=>['required','distinct','in:home,office,mobile'],
//                'phone.*.number'=>['required','string'],
//                'phone.*.country_code'=>['required','string'],
//                'company_name'=>['sometimes','string'],
//                'sales_agent_id'=>['sometimes','string'],
//                'source_of_lead'=>['sometimes','string'],
//                'tags'=>['sometimes','array'],
//                'tags.*'=>['alpha_num'],
//                'website'=>['sometimes','array','distinct'],
//                'website.*'=>['url']
            ],

            'update' => [
                'first_name' => ['sometimes', 'required'],
                'last_name' => ['sometimes', 'required'],
                'middle_name' => ['sometimes', 'required'],
                'email' => ['sometimes', 'required', 'array', 'min:1', 'max:2'],
                'email.*.type' => ['required', 'in:home,office'],
                'email.*.value' => ['email', 'required'],
                'phone' => ['sometimes', 'required', 'array', 'min:1', 'max:3'],
                'phone.*.type' => ['required'],
                'phone.*.number' => ['required'],
                'phone.*.country_code' => ['required'],
                'company_name' => ['sometimes', 'required'],
                'sales_agent_id' => ['sometimes', 'required'],
                'source_of_lead' => ['sometimes', 'required'],
                'tags'=>['sometimes','array'],
                'tags.*'=>['alpha_num'],
                'website'=>['sometimes','array','distinct'],
                'website.*'=>['url']
            ]
        ];
    }
}