<?php

namespace Tests\Unit\User;

use App\Exceptions\EmailAlreadyExistsException;
use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use App\Services\MerchantSettingsService;
use App\Services\UserService;
use App\Validators\MerchantSettingValidation;
use App\Validators\UserValidation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var UserService;
     */
    protected $userService;

    /**
     * @var UserValidation;
     */
    protected $userValidation;

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

    public function setUp(): void
    {
        parent::setUp();

        $this->userValidation = new UserValidation();
        $this->userService = new UserService($this->userValidation);

        $this->customerRoleId = UserRole::where('name', UserRole::CUSTOMER)->first()->id;
        $this->merchantRoleId = UserRole::where('name', UserRole::MERCHANT)->first()->id;

        $this->paymentServiceProviderEverypayId = PaymentServiceProvider::where('name', PaymentServiceProvider::EVERYPAY)->first()->id;
        $this->paymentServiceProviderStripeId = PaymentServiceProvider::where('name', PaymentServiceProvider::STRIPE)->first()->id;

        $this->merchantEverypay = Merchant::factory()->create(['user_role_id' => $this->merchantRoleId]);
    }

    public function testRegisterLoginLogoutCustomerSuccess()
    {
        // register
        $registerPayload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->customerRoleId,
        ];

        $response = $this->userService->register($registerPayload);

        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('user', $response);

        $user = User::where('email', 'test@test.com')->first();

        $this->assertEquals($registerPayload['name'], $user->name);
        $this->assertEquals($registerPayload['email'], $user->email);
        $this->assertEquals($registerPayload['user_role_id'], $user->user_role_id);
        $this->assertTrue(Hash::check($registerPayload['password'], $user->password));

        // login
        $loginPayload = [
            'email' => 'test@test.com',
            'password' => 'test1234',
        ];

        $response = $this->userService->login($loginPayload);

        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('user', $response);
        $this->assertEquals($registerPayload['name'], $response['user']->name);
        $this->assertEquals($registerPayload['email'], $response['user']->email);
        $this->assertEquals($registerPayload['user_role_id'], $response['user']->user_role_id);
        $this->assertTrue(Hash::check($registerPayload['password'], $response['user']->password));

        // logout
        $this->helper->login($user);
        $this->userService->logout($user);
    }

    public function testRegisterCustomerValidationFail()
    {

        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->customerRoleId,
            'merchant_settings' => [
                'psp_api_key' => 'test12345678',
                'payment_service_provider_id' => $this->paymentServiceProviderEverypayId,
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->userService->register($payload);
    }

    public function testRegisterMerchantSuccess()
    {
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->merchantRoleId,
            'merchant_settings' => [
                'psp_api_key' => 'test12345678',
                'payment_service_provider_id' => $this->paymentServiceProviderEverypayId,
            ]
        ];

        $response = $this->userService->register($payload);

        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('user', $response);

        $merchant = Merchant::where('email', 'test@test.com')->first();

        $this->assertEquals($payload['name'], $merchant->name);
        $this->assertEquals($payload['email'], $merchant->email);
        $this->assertEquals($payload['user_role_id'], $merchant->user_role_id);
        $this->assertTrue(Hash::check($payload['password'], $merchant->password));

        $this->assertEquals($payload['merchant_settings']['psp_api_key'], $merchant->merchantSettings->psp_api_key);
        $this->assertEquals($payload['merchant_settings']['payment_service_provider_id'], $merchant->merchantSettings->payment_service_provider_id);
    }

    public function testRegisterMerchantValidationFail()
    {
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->merchantRoleId
        ];

        $this->expectException(ValidationException::class);

        $this->userService->register($payload);
    }

    public function testRegisterUserEmailAlreadyExistsException()
    {
        // register
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->customerRoleId,
        ];

        $this->userService->register($payload);

        // register with the same email
        $this->expectException(EmailAlreadyExistsException::class);
        $this->userService->register($payload);
    }

    /**
     * @dataProvider provideEmails
     */
    public function testEmailAlreadyExists($registerEmail, $checkEmail)
    {
        // register
        $payload = [
            'name' => 'test_name',
            'email' => $registerEmail,
            'password' => 'test1234',
            'user_role_id' => $this->customerRoleId,
        ];

        $this->userService->register($payload);

        $class = new \ReflectionClass(UserService::class);
        $method = $class->getMethod('emailAlreadyExists');

        $result = $method->invokeArgs($this->userService, [$checkEmail]);

        if ($registerEmail === $checkEmail) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public static function provideEmails()
    {
        return [
            ['test@test.com', 'test@test.com'],
            ['test@test.com', 'test2@test.com'],
        ];
    }
}
