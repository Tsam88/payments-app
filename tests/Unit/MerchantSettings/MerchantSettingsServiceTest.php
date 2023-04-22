<?php

namespace Tests\Unit\MerchantSettings;

use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\PaymentServiceProvider;
use App\Models\UserRole;
use App\Services\MerchantSettingsService;
use App\Validators\MerchantSettingValidation;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MerchantSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var MerchantSettingsService;
     */
    protected $merchantSettingsService;

    /**
     * @var MerchantSettingValidation;
     */
    protected $merchantSettingValidation;

    /**
     * @var int
     */
    private $merchantRoleId;

    /**
     * @var int
     */
    private $paymentServiceProviderEverypayId;

    /**
     * @var int
     */
    private $paymentServiceProviderStripeId;

    /**
     * @var Merchant
     */
    private $merchantEverypay;

    /**
     * @var MerchantSetting
     */
    private $merchantSettingsEverypay;

    public function setUp(): void
    {
        parent::setUp();

        $this->merchantSettingValidation = new MerchantSettingValidation();
        $this->merchantSettingsService = new MerchantSettingsService($this->merchantSettingValidation);

        $this->merchantRoleId = UserRole::where('name', UserRole::MERCHANT)->first()->id;

        $this->paymentServiceProviderEverypayId = PaymentServiceProvider::where('name', PaymentServiceProvider::EVERYPAY)->first()->id;
        $this->paymentServiceProviderStripeId = PaymentServiceProvider::where('name', PaymentServiceProvider::STRIPE)->first()->id;

        $this->merchantEverypay = Merchant::factory()->create(['user_role_id' => $this->merchantRoleId]);

        $this->merchantSettingsEverypay = MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantEverypay->id,
            'payment_service_provider_id' => $this->paymentServiceProviderEverypayId,
        ]);
    }

    public function testUpdateMerchantSettingsSuccess()
    {
        $payload = [
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
            'psp_api_key' => 'test1234',
        ];

        $this->merchantSettingsService->update($payload, $this->merchantSettingsEverypay);

        $expected = [
            "psp_api_key" => "test1234",
            "merchant_id" => $this->merchantEverypay->id,
            "payment_service_provider_id" => $this->paymentServiceProviderStripeId,
            "id" => $this->merchantSettingsEverypay->id,
        ];

        $this->assertEquals($expected, $this->merchantSettingsEverypay->toArray());
    }

    public function testUpdateMerchantSettingsValidationException()
    {
        $payload = [
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
        ];

        $this->expectException(ValidationException::class);

        $this->merchantSettingsService->update($payload, $this->merchantSettingsEverypay);
    }
}
