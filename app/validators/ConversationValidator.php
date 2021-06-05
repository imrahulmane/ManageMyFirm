<?php

namespace App\validators;

use App\util\BaseValidator;

class ConversationValidator extends BaseValidator
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'conversation';
    }

    protected function rules()
    {
        // TODO: Implement rules() method.
        return [
            'add' => [
                'conversation_message' => ['required', 'string'],
                'customer_id' => ['required', 'string'],
                'reminder_date' => ['required', 'string'],
            ],
            'update' => [
                'conversation_message' => ['sometimes', 'string'],
                'reminder_date' => ['sometimes', 'string'],
            ]
        ];
    }
}