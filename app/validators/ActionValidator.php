<?php


namespace App\validators;


use App\util\BaseValidator;

class ActionValidator extends BaseValidator
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'action';
    }

    protected function rules()
    {
        // TODO: Implement rules() method.
        return [
            'add' => [
                'action_message' => ['required', 'string'],
                'customer_id' => ['required', 'string'],
                'action_date' => ['required', 'string']
            ],
            'update' => [
                'action_message' => ['sometimes', 'string'],
                'action_date' => ['sometimes', 'string']
            ]
        ];
    }
}