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
                'address' => ['required', 'string'],
                'services.*.item_id' => ['required', 'string'],
                'services.*.start_date_time' => ['required', 'string'],
                'services.*.end_date_time' => ['required', 'string'],
                'services.*.quantity' => ['required', 'numeric'],
            ],
            'update' => [
                'cust_id' => ['sometimes','required', 'string'],
                'address' => ['sometimes','required', 'string'],
                'services.*.item_id' => ['sometimes','required', 'string'],
                'services.*.start_date_time' => ['sometimes','required', 'string'],
                'services.*.end_date_time' => ['sometimes','required', 'string'],
                'services.*.quantity' => ['sometimes','required','numeric'],
            ]
        ];
    }
}