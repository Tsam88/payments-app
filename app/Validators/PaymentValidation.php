<?php

declare(strict_types = 1);

namespace App\Validators;

use App\Models\UserRole;

class PaymentValidation extends AbstractValidation
{
    /**
     * The basic validation rules of model
     *
     * @var array
     */
    private const VALIDATION_RULES = [
        'card' => [
            'array',
            'required',
        ],
        'card.card_number' => [
            'required',
            'string',
            'size:16',
        ],
        'card.expiration_date' => [
            'required',
            'date_format:m/Y'
        ],
        'card.cvv' => [
            'required',
            'int',
            'digits:3',
        ],
        'card.cardholder_name' => [
            'required',
            'string',
            'max:50'
        ],
        'amount' => [
            'required',
            'numeric',
            'gt:0'
        ],
    ];

    /**
     * User register validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function createPayment(array $input)
    {
        $userRoleIds = UserRole::pluck('id')->toArray();

        // build the rules for register
        $validationRules = [
            'card' => $this->getRule(self::VALIDATION_RULES, 'card', []),
            'card.card_number' => $this->getRule(self::VALIDATION_RULES, 'card.card_number', []),
            'card.expiration_date' => $this->getRule(self::VALIDATION_RULES, 'card.expiration_date', []),
            'card.cvv' => $this->getRule(self::VALIDATION_RULES, 'card.cvv', []),
            'card.cardholder_name' => $this->getRule(self::VALIDATION_RULES, 'card.cardholder_name', []),
            'amount' => $this->getRule(self::VALIDATION_RULES, 'amount', []),
        ];

        $validator = $this->getValidator($input, $validationRules);
        $data = $validator->validate();

        return $data;
    }
}
