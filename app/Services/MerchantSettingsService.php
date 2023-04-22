<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\MerchantSetting;
use App\Validators\MerchantSettingValidation;

class MerchantSettingsService
{
    /**
     * @var MerchantSettingValidation
     */
    private $merchantSettingValidation;

    public function __construct(MerchantSettingValidation $merchantSettingValidation)
    {
        $this->merchantSettingValidation = $merchantSettingValidation;
    }

    /**
     * Update merchant settings
     *
     * @param array           $input
     * @param MerchantSetting $merchantSettings
     *
     * @return void
     */
    public function update(array $input, MerchantSetting $merchantSettings): void
    {
        // data validation
        $data = $this->merchantSettingValidation->update($input);

        try {
            // update merchant settings
            $merchantSettings->update($data);
        } catch (\Exception $e) {
            // something went wrong, throw exception
            throw $e;
        }
    }
}
