<?php

namespace Tests\Unit\Payment;

use App\Clients\StripePaymentClient;
use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper;

class StripePaymentClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var TestHelper;
     */
    protected $helper;

    /**
     * @var StripePaymentClient;
     */

    protected $stripePaymentClient;
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
    private $paymentServiceProviderStripeId;

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

        $this->paymentServiceProviderStripeId = PaymentServiceProvider::where('name', PaymentServiceProvider::STRIPE)->first()->id;

        $this->merchantStripe = Merchant::factory()->create(['user_role_id' => $this->merchantRoleId]);

        MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantStripe->id,
            'payment_service_provider_id' => $this->paymentServiceProviderStripeId,
        ]);

        $this->customer = User::factory()->create(['user_role_id' => $this->customerRoleId]);

        $this->helper->login($this->customer);

        $this->stripePaymentClient = new StripePaymentClient();
    }

    public function testSetApiKey()
    {
        $mockBuilder = \Mockery::mock('overload:Stripe');
        $mockBuilder->shouldReceive('setApiKey')->with($this->merchantStripe->merchantSettings->psp_api_key);

        $this->stripePaymentClient->setApiKey($this->merchantStripe);

        // we add this just to make a dummy assertion
        $this->addToAssertionCount(1);
    }

    public function testCharge()
    {
        $payload = [
            'amount' => 150,
            'currency' => 'eur',
            'source' => 'tok_visa',
            'description' => 'test',
        ];

        $mockBuilder = \Mockery::mock('overload:Stripe\Charge');
        $mockBuilder->shouldReceive('create')->with($payload)->andReturn([]);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $this->stripePaymentClient->charge($payload);

        // we add this just to make a dummy assertion
        $this->addToAssertionCount(1);
    }

    public function testCreatePayment()
    {
        $payload = [
            'amount' => 150,
            'currency' => 'eur',
            'source' => 'tok_visa',
            'description' => 'test',
        ];

        $mockBuilder = \Mockery::mock('overload:Stripe');
        $mockBuilder->shouldReceive('setApiKey')->with($this->merchantStripe->merchantSettings->psp_api_key);

        $mockBuilder2 = \Mockery::mock('overload:Stripe\Charge');
        $mockBuilder2->shouldReceive('create')->with($payload)->andReturn([]);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $this->stripePaymentClient->createPayment($payload, $this->customer, $this->merchantStripe);

        // we add this just to make a dummy assertion
        $this->addToAssertionCount(1);
    }
}
