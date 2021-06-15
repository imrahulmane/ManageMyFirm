<?php


namespace App\validators;


use App\util\BaseValidator;

class ItemValidator extends BaseValidator
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'items';
    }

    protected function rules()
    {
        // TODO: Implement rules() method.
        return [
            'add' => [
                'type' => ['required', 'string'],
                'cost_per_hr' => ['required', 'string']
            ],
            'update' => [
                'type' => ['sometimes', 'string'],
                'cost_per_hr' => ['sometimes', 'string']
            ]
        ];
    }
}