<?php

namespace Tests\Feature\Payment;

use App\Contracts\PaymentContract;
use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper;

class PaymentTest extends TestCase
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

        MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantEverypay->id,
            'payment_service_provider_id' => $this->paymentServiceProviderEverypayId,
        ]);
        MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantStripe->id,
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
        ]);

        $this->customer = User::factory()->create(['user_role_id' => $this->customerRoleId]);

        $this->helper->login($this->customer);
    }

    public function testPaymentEverypayMerchantSuccess()
    {
        $mockBuilder = $this->getMockBuilder(PaymentContract::class)
            ->onlyMethods(['createPayment', 'setApiKey', 'charge'])->getMock();
        $this->app->instance(PaymentContract::class, $mockBuilder);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('payments.create', ['merchant' => $this->merchantEverypay->id]), $payload)
            ->assertStatus(200);
    }

    public function testPaymentStripeMerchantSuccess()
    {
        $mockBuilder = $this->getMockBuilder(PaymentContract::class)
            ->onlyMethods(['createPayment', 'setApiKey', 'charge'])->getMock();
        $this->app->instance(PaymentContract::class, $mockBuilder);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('payments.create', ['merchant' => $this->merchantStripe->id]), $payload)
            ->assertStatus(200);
    }

    public function testPaymentFail()
    {
        $mockBuilder = $this->getMockBuilder(PaymentContract::class)
            ->onlyMethods(['createPayment', 'setApiKey', 'charge'])->getMock();
        $this->app->instance(PaymentContract::class, $mockBuilder);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('payments.create', ['merchant' => $this->merchantEverypay->id]), $payload)
            ->assertStatus(422);

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('payments.create', ['merchant' => 9999]), $payload)
            ->assertStatus(404);
    }
}
