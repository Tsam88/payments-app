<?php

declare(strict_types = 1);

namespace App\Validators;

use App\Models\PaymentServiceProvider;
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
        'merchant_settings' => [
            'array',
            'nullable',
        ],
        'merchant_settings.psp_api_key' => [
            'string',
            'max:255',
        ],
        'merchant_settings.payment_service_provider_id' => [
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
        $paymentServiceProviderIds = PaymentServiceProvider::pluck('id')->toArray();
        $merchantRoleId = UserRole::where('name', UserRole::MERCHANT)->first()->id;

        // build the rules for register
        $validationRules = [
            'name' => $this->getRule(self::VALIDATION_RULES, 'name', []),
            'email' => $this->getRule(self::VALIDATION_RULES, 'email', []),
            'password' => $this->getRule(self::VALIDATION_RULES, 'password', []),
            'user_role_id' => $this->getRule(self::VALIDATION_RULES, 'user_role_id', ['in:'.implode(',', $userRoleIds)]),
            'merchant_settings' => $this->getRule(self::VALIDATION_RULES, 'merchant_settings', [
                "required_if:user_role_id, {$merchantRoleId}",
                "prohibited_unless:user_role_id, {$merchantRoleId}",
            ]),
            'merchant_settings.psp_api_key' => $this->getRule(self::VALIDATION_RULES, 'merchant_settings.psp_api_key', [
                "required_if:user_role_id, {$merchantRoleId}",
                "prohibited_unless:user_role_id, {$merchantRoleId}",
            ]),
            'merchant_settings.payment_service_provider_id' => $this->getRule(self::VALIDATION_RULES, 'merchant_settings.payment_service_provider_id', [
                'in:'.implode(',', $paymentServiceProviderIds),
                "required_if:user_role_id, {$merchantRoleId}",
                "prohibited_unless:user_role_id, {$merchantRoleId}",
            ]),
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
