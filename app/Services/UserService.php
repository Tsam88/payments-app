<?php

declare(strict_types = 1);

namespace App\Services;

use App\Exceptions\EmailAlreadyExistsException;
use App\Models\Merchant;
use App\Models\MerchantSetting;
use App\Models\User;
use App\Models\UserRole;
use App\Validators\UserValidation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @var UserValidation
     */
    private $userValidation;

    public function __construct(UserValidation $userValidation)
    {
        $this->userValidation = $userValidation;
    }

    /**
     * Register a user
     *
     * @param array $input
     *
     * @return array
     */
    public function register(array $input): array
    {
        // data validation
        $data = $this->userValidation->register($input);

        // we need to lowercase email if exists, to validate uniqueness later
        if (!empty($input['email'])) {
            $input['email'] = \mb_convert_case(\trim($input['email']), MB_CASE_LOWER, 'UTF-8');
        }

        // check if email already exists in db
        if ($this->emailAlreadyExists($input['email'])) {
            throw new EmailAlreadyExistsException();
        }

        // start db transaction
        DB::beginTransaction();

        try {
            $data['password'] = bcrypt($data['password']);

            $userRole = UserRole::where('id', $data['user_role_id'])->first();

            // if new user is merchant, then create merchant settings
            if ($userRole->name === UserRole::MERCHANT) {
                $user = Merchant::create($data);

                MerchantSetting::create([
                    'merchant_id' => $user->id,
                    'psp_api_key' => $data['merchant_settings']['psp_api_key'],
                    'payment_service_provider_id' => $data['merchant_settings']['payment_service_provider_id'],
                ]);
            } else {
                $user = User::create($data);
            }

            event(new Registered($user));

            auth()->login($user);

            $token = $user->createToken("API TOKEN")->plainTextToken;

        } catch (\Exception $e) {
            // something went wrong, rollback and throw same exception
            DB::rollBack();

            throw $e;
        }

        // commit database changes
        DB::commit();

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * Login user to app
     * Return an array that contains an authorization token
     *
     * @param array $input
     *
     * @return array
     */
    public function login(array $input): array
    {
        // data validation
        $data = $this->userValidation->login($input);

        $data['email'] = \mb_convert_case($input['email'], MB_CASE_LOWER, 'UTF-8');

        $user = User::where('email', $data['email'])->first();

        if (null == $user || !Hash::check($data['password'], $user->password)) {
            throw new AuthenticationException();
        }

        $token = $user->createToken("API TOKEN")->plainTextToken;

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * Logout user
     *
     * @param User $user
     *
     * @return void
     */
    public function logout(User $user): void
    {
        $user->token()->revoke();
    }

    /**
     * Get user role
     *
     * @param User $user
     *
     * @return string
     */
    public function getUserRole(User $user): string
    {
        return $user->userRole->name;
    }

    /**
     * Check if email already exists in db.
     *
     * @param string $email
     *
     * @return bool
     */
    private function emailAlreadyExists(string $email): bool
    {
        $email = \mb_convert_case(\trim($email), MB_CASE_LOWER, 'UTF-8');

        $emailExists = User::where('email', $email)->exists();

        return $emailExists;
    }
}
