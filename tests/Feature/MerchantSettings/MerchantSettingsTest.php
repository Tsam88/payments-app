<?php

namespace Tests\Feature\MerchantSettings;

use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper;

class MerchantSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var TestHelper;
     */
    protected $helper;

    /**
     * @var int
     */
    private $customerRoleId;

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
     * @var Merchant
     */
    private $merchantStripe;

    /**
     * @var MerchantSetting
     */
    private $merchantSettingsEverypay;

    /**
     * @var MerchantSetting
     */
    private $merchantSettingsStripe;

    /**
     * @var User
     */
    private $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerRoleId = UserRole::where('name', UserRole::CUSTOMER)->first()->id;
        $this->merchantRoleId = UserRole::where('name', UserRole::MERCHANT)->first()->id;

        $this->paymentServiceProviderEverypayId = PaymentServiceProvider::where('name', PaymentServiceProvider::EVERYPAY)->first()->id;
        $this->paymentServiceProviderStripeId = PaymentServiceProvider::where('name', PaymentServiceProvider::STRIPE)->first()->id;

        $this->merchantEverypay = Merchant::factory()->create(['user_role_id' => $this->merchantRoleId]);
        $this->merchantStripe = Merchant::factory()->create(['user_role_id' => $this->merchantRoleId]);

        $this->merchantSettingsEverypay = MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantEverypay->id,
            'payment_service_provider_id' => $this->paymentServiceProviderEverypayId,
        ]);
        $this->merchantSettingsStripe = MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantStripe->id,
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
        ]);

        $this->customer = User::factory()->create(['user_role_id' => $this->customerRoleId]);
    }

    public function testUpdateMerchantSettingsSuccess()
    {
        $this->helper->login($this->merchantEverypay);

        $payload = [
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
            'psp_api_key' => '1234',
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('PATCH', route('merchant-settings.update', ['merchantSettings' => $this->merchantSettingsEverypay->id]), $payload)
            ->assertStatus(204);
    }

    public function testUpdateMerchantSettingsUsingOtherMerchantFail()
    {
        $this->helper->login($this->merchantEverypay);

        $payload = [
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
            'psp_api_key' => '1234',
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('PATCH', route('merchant-settings.update', ['merchantSettings' => $this->merchantSettingsStripe->id]), $payload)
            ->assertStatus(404);
    }

    public function testUpdateMerchantSettingsUsingCustomerFail()
    {
        $this->helper->login($this->customer);

        $payload = [
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
            'psp_api_key' => '1234',
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('PATCH', route('merchant-settings.update', ['merchantSettings' => $this->merchantSettingsStripe->id]), $payload)
            ->assertStatus(404);
    }

    public function testUpdateMerchantSettingsValidationError()
    {
        $this->helper->login($this->merchantEverypay);

        $payload = [
            'payment_service_provider_id' => 9999,
            'psp_api_key' => '1234',
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('PATCH', route('merchant-settings.update', ['merchantSettings' => $this->merchantSettingsEverypay->id]), $payload)
            ->assertStatus(422);
    }
}
