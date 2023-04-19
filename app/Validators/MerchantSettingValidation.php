<?php

declare(strict_types = 1);

namespace App\Validators;

use App\Models\PaymentServiceProvider;

class MerchantSettingValidation extends AbstractValidation
{
    /**
     * The basic validation rules of model
     *
     * @var array
     */
    private const VALIDATION_RULES = [
        'psp_api_key' => [
            'required',
            'string',
            'max:255',
        ],
        'payment_service_provider_id' => [
            'required',
            'integer',
        ],
    ];

    /**
     * Update merchant settings validation.
     *
     * @param array $input
     *
     * @return array
     */
    public function update(array $input)
    {
        $paymentServiceProviderIds = PaymentServiceProvider::pluck('id')->toArray();

        // build the rules for register
        $validationRules = [
            'psp_api_key' => $this->getRule(self::VALIDATION_RULES, 'psp_api_key', []),
            'payment_service_provider_id' => $this->getRule(self::VALIDATION_RULES, 'payment_service_provider_id', [
                'in:'.implode(',', $paymentServiceProviderIds),
            ]),
        ];

        $validator = $this->getValidator($input, $validationRules);
        $data = $validator->validate();

        return $data;
    }
}
