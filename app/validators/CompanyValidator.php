<?php


namespace App\validators;


use App\util\BaseValidator;

class CompanyValidator extends BaseValidator
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'company';
    }

    protected function rules()
    {
        // TODO: Implement rules() method.
        return [
            'add' => [
                'name' => ['required', 'string'],
                'website' => ['required', 'url'],
                'phone' => ['required', 'string', 'size:10'],
                'support_email' => ['required', 'email']
            ],
            'update' => [
                'name' => ['sometimes','nullable','string'],
                'website' => ['sometimes', 'url'],
                'phone' => ['sometimes', 'string', 'size:10'],
                'support_email' => ['sometimes', 'email']
            ]
        ];
    }
}