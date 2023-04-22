<?php

namespace Tests\Feature\User;

use App\Models\PaymentServiceProvider;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

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
    private $paymentServiceProviderId;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerRoleId = UserRole::where('name', UserRole::CUSTOMER)->first()->id;
        $this->merchantRoleId = UserRole::where('name', UserRole::MERCHANT)->first()->id;
        $this->paymentServiceProviderId = PaymentServiceProvider::where('name', PaymentServiceProvider::EVERYPAY)->first()->id;
    }

    public function testCustomerRegisterSuccess()
    {
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->customerRoleId,
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('users.register'), $payload)
            ->assertStatus(200);
    }

    public function testCustomerRegisterFail()
    {
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->customerRoleId,
            'merchant_settings' => [
                'psp_api_key' => 'test1234',
                'payment_service_provider_id' => $this->paymentServiceProviderId,
            ],
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('users.register'), $payload)
            ->assertStatus(422);
    }

    public function testMerchantRegisterSuccess()
    {
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->merchantRoleId,
            'merchant_settings' => [
                'psp_api_key' => 'test1234',
                'payment_service_provider_id' => $this->paymentServiceProviderId,
            ],
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('users.register'), $payload)
            ->assertStatus(200);
    }

    public function testMerchantRegisterFail()
    {
        $payload = [
            'name' => 'test_name',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'user_role_id' => $this->merchantRoleId,
        ];

        $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('users.register'), $payload)
            ->assertStatus(422);
    }

    public function testLoginLogout()
    {
        $password = 'secret1234';

        $user = User::factory()->create([
            'password' => bcrypt($password),
            'user_role_id' => $this->customerRoleId,
        ]);

        $loginPayload = [
            'email' => $user->email,
            'password' => $password,
            'grant_type' => 'password',
        ];

        $loginResponse = $this->withHeaders(static::JSON_HEADERS)
            ->json('POST', route('users.login'), $loginPayload)
            ->assertStatus(200);

        $headers = ['Authorization' => 'Bearer ' . $loginResponse->json()['token']];
        $this->withHeaders($headers)
            ->json('POST', route('users.logout'))
            ->assertStatus(204);
    }
}
