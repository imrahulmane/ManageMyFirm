<?php


namespace App\validators;


use App\util\BaseValidator;

class ServiceValidator extends BaseValidator
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'services';
    }

    protected function rules()
    {
        // TODO: Implement rules() method.
        return [
            'add' => [
                'cust_id' => ['required', 'string'],
                'item_id' => ['required', 'string'],
                'start_date_time' => ['required', 'string'],
                'end_date_time' => ['required', 'string'],
                'address' => ['required', 'string']
            ],
            'update' => [
                'item_id' => ['sometimes', 'string'],
                'start_date_time' => ['sometimes', 'string'],
                'end_date_time' => ['sometimes', 'string'],
                'address' => ['sometimes', 'string']
            ]
        ];
    }
}