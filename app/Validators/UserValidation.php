<?php

declare(strict_types = 1);

namespace App\Validators;

use App\Models\UserRole;

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
        'email' => [
            'required',
            'email',
            'max:255',
        ],
        'password' => [
            'required',
            'string',
            'min:8',
        ],
        'user_role_id' => [
            'required',
            'integer',
        ],
    ];

    /**
     * User register validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function register(array $input)
    {
        $userRoleIds = UserRole::pluck('id')->toArray();

        // build the rules for register
        $validationRules = [
            'name' => $this->getRule(self::VALIDATION_RULES, 'name', []),
            'email' => $this->getRule(self::VALIDATION_RULES, 'email', []),
            'password' => $this->getRule(self::VALIDATION_RULES, 'password', []),
            'user_role_id' => $this->getRule(self::VALIDATION_RULES, 'user_role_id', ['in:'.implode(',', $userRoleIds)]),
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
    public function login(array $input)
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
