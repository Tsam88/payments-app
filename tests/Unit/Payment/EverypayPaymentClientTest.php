<?php

namespace Tests\Unit\Payment;

use App\Clients\EverypayPaymentClient;
use App\Exceptions\EverypayException;
use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper;

class EverypayPaymentClientTest extends TestCase
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
     * @var Merchant
     */
    private $merchantEverypay;

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

        $this->merchantEverypay = Merchant::factory()->create(['user_role_id' => $this->merchantRoleId]);

        MerchantSetting::factory()->create([
            'merchant_id' => $this->merchantEverypay->id,
            'payment_service_provider_id' => $this->paymentServiceProviderEverypayId,
        ]);

        $this->customer = User::factory()->create(['user_role_id' => $this->customerRoleId]);

        $this->helper->login($this->customer);

        $this->everypayPaymentClient = new EverypayPaymentClient();
    }

    public function testSetApiKey()
    {
        $mockBuilder = \Mockery::mock('overload:Everypay\Everypay');
        $mockBuilder->shouldReceive('setApiKey')->with($this->merchantEverypay->merchantSettings->psp_api_key);

        $this->everypayPaymentClient->setApiKey($this->merchantEverypay);

        // we add this just to make a dummy assertion
        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider provideEverypayChargeResponse
     */
    public function testCharge($everypayChargeResponse)
    {
        $payload = [
            'card_number' => '1111222233334444',
            'expiration_month' => '12',
            'expiration_year' => '2023',
            'cvv' => '123',
            'holder_name' => 'John Doe',
            'amount' => 150
        ];

        $mockBuilder = \Mockery::mock('overload:Everypay\Payment');
        $mockBuilder->shouldReceive('create')->with($payload)->andReturn($everypayChargeResponse);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        if (isset($everypayChargeResponse->error)) {
            $this->expectException(EverypayException::class);
        } else {
            // we add this just to make a dummy assertion
            $this->addToAssertionCount(1);
        }

        $this->everypayPaymentClient->charge($payload);
    }

    public static function provideEverypayChargeResponse()
    {
        return [
            [[]],
            [(object) ['error' => (object) ['status'=> 400, 'code' => 20001, 'message' => 'Expiration year in the past or invalid.']]],
        ];
    }

    public function testCreatePayment()
    {
        $payload = [
            'card_number' => '1111222233334444',
            'expiration_month' => '12',
            'expiration_year' => '2023',
            'cvv' => '123',
            'holder_name' => 'John Doe',
            'amount' => 150
        ];

        $mockBuilder = \Mockery::mock('overload:Everypay\Everypay');
        $mockBuilder->shouldReceive('setApiKey')->with($this->merchantEverypay->merchantSettings->psp_api_key);


//        $mockBuilder::$isTest = true;
//        $test = $mockBuilder->mockery_getMockableProperties();
//
//        dd($test);

//        $mockBuilder2 = \Mockery::mock('overload:Everypay\Everypay');
//        $mockBuilder2->shouldReceive('$isTest')->with(true);

        $mockBuilder3 = \Mockery::mock('overload:Everypay\Payment');
        $mockBuilder3->shouldReceive('create')->with($payload)->andReturn([]);

        $payload = [
            'card' => [
                'card_number' => '1111222233334444',
                'expiration_date' => '12/2023',
                'cvv' => 123,
                'cardholder_name' => 'John Doe',
            ],
            'amount' => 1.5,
        ];

        $this->everypayPaymentClient->createPayment($payload, $this->customer, $this->merchantEverypay);

        // we add this just to make a dummy assertion
        $this->addToAssertionCount(1);
    }
}
