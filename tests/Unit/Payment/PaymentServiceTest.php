<?php

namespace Tests\Unit\Payment;

use App\Clients\EverypayPaymentClient;
use App\Clients\StripePaymentClient;
use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\Payment;
use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use App\Services\PaymentService;
use App\Validators\PaymentValidation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var TestHelper;
     */
    protected $helper;

    /**
     * @var EverypayPaymentClient;
     */
    protected $everypayPaymentClient;

    /**
     * @var StripePaymentClient;
     */
    protected $stripePaymentClient;

    /**
     * @var PaymentValidation;
     */
    protected $paymentValidation;

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

        $this->everypayPaymentClient = new EverypayPaymentClient();
        $this->stripePaymentClient = new StripePaymentClient();
        $this->paymentValidation = new PaymentValidation();

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

    public function testCreatePaymentWithEverypayMerchant()
    {
        $paymentService = new PaymentService($this->paymentValidation, $this->everypayPaymentClient);

        $payload = [
            'card_number' => '1111222233334444',
            'expiration_month' => '12',
            'expiration_year' => '2023',
            'cvv' => '123',
            'holder_name' => 'John Doe',
            'amount' => 150
        ];

        $mockBuilder = \Mockery::mock('overload:Everypay\Payment');
        $mockBuilder->shouldReceive('create')->with($payload)->andReturn([]);

        $mockBuilder2 = \Mockery::mock('overload:Everypay\Everypay');
        $mockBuilder2->shouldReceive('setApiKey')->with($this->merchantEverypay->merchantSettings->psp_api_key);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $mockBuilder3 = \Mockery::mock(EverypayPaymentClient::class);
//        $mockBuilder3->shouldReceive(['createPayment', 'setApiKey', 'charge']);
        $mockBuilder3->shouldReceive('createPayment')
//            ->once()
            ->with($payload, $this->customer, $this->merchantEverypay);

        $paymentService->createPayment($payload, $this->customer, $this->merchantEverypay);

        $expected = [
            "id" => 1,
            "user_id" => 5,
            "merchant_id" => 3,
            "payment_service_provider_id" => 2,
            "amount" => 1.5,
            "last4" => "4444",
            "expiration_date" => "12/2023",
            "cardholder_name" => "John Doe",
        ];

        $payment = Payment::where('user_id', $this->customer->id)
            ->where('merchant_id', $this->merchantEverypay->id)
            ->first();

        $this->assertEquals($expected, $payment->toArray());
    }

    public function testCreatePaymentWithStripeMerchant()
    {
        $paymentService = new PaymentService($this->paymentValidation, $this->stripePaymentClient);

        $payload = [
            'amount' => 150,
            'currency' => 'eur',
            'source' => 'tok_visa',
            'description' => 'test'
        ];

        $mockBuilder = \Mockery::mock('overload:Stripe\Charge');
        $mockBuilder->shouldReceive('create')->with($payload)->andReturn([]);

        $mockBuilder2 = \Mockery::mock('overload:Stripe');
        $mockBuilder2->shouldReceive('setApiKey')->with($this->merchantStripe->merchantSettings->psp_api_key);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $mockBuilder3 = \Mockery::mock(StripePaymentClient::class);
        $mockBuilder3->shouldReceive('createPayment')
//            ->once()
            ->with($payload, $this->customer, $this->merchantStripe);

        $paymentService->createPayment($payload, $this->customer, $this->merchantStripe);

        $expected = [
            "id" => 1,
            "user_id" => 5,
            "merchant_id" => 4,
            "payment_service_provider_id" => 1,
            "amount" => 1.5,
            "last4" => "4444",
            "expiration_date" => "12/2023",
            "cardholder_name" => "John Doe",
        ];

        $payment = Payment::where('user_id', $this->customer->id)
            ->where('merchant_id', $this->merchantStripe->id)
            ->first();

        $this->assertEquals($expected, $payment->toArray());
    }
}
