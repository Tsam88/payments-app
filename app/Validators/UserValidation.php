<?php

declare(strict_types = 1);

namespace App\Validators;

class UserValidation extends AbstractValidation
{
    /**
     * The basic validation rules of model
     *
     * @var array
     */
    private const VALIDATION_RULES = [
        'name' => [
            'required',
            'string',
            'max:255',
        ],
        'surname' => [
            'required',
            'string',
            'max:255',
        ],
        'email' => [
            'required',
            'email',
            'max:255',
        ],
        'password' => [
            'required',
            'string',
        ],
        'phone_number' => [
            'string',
            'max:255',
            'nullable',
        ],
        'token' => [
            'string'
        ],
        'items_per_page' => [
            'integer',
            'gt:0',
        ],
    ];

    /**
     * User register validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function userRegister(array $input)
    {
        // build the rules for register
        $validationRules = [
            'name' => $this->getRule(self::VALIDATION_RULES, 'name', []),
            'email' => $this->getRule(self::VALIDATION_RULES, 'email', []),
            'password' => $this->getRule(self::VALIDATION_RULES, 'password', ['min:8']),
        ];

        $validator = $this->getValidator($input, $validationRules);
        $data = $validator->validate();

        return $data;
    }

    /**
     * User login validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function userLogin(array $input)
    {
        // build the rules for login
        $validationRules = [
            'email' => $this->getRule(self::VALIDATION_RULES, 'email', []),
            'password' => $this->getRule(self::VALIDATION_RULES, 'password', []),
        ];

        $validator = $this->getValidator($input, $validationRules);
        $data = $validator->validate();

        return $data;
    }
}
