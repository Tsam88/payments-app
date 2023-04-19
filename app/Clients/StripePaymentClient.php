<?php

declare(strict_types = 1);

namespace App\Clients;

use App\Models\Merchant;
use App\Models\User;
use Stripe\Charge;
use Stripe\Stripe;

class StripePaymentClient extends PaymentClient
{
    /**
     * Create a payment
     *
     * @param array     $input
     * @param User      $user
     * @param Merchant  $merchant
     *
     * @return void
     */
    public function createPayment(array $input, User $user, Merchant $merchant): void
    {
        // set api key
        $this->setApiKey($merchant);

        // create a charge
        $this->charge($input);

        var_dump('STRIPE');
    }

    /**
     * Set api key
     *
     * @param Merchant $merchant
     *
     * @return void
     */
    public function setApiKey(Merchant $merchant): void
    {
        Stripe::setApiKey($merchant->merchantSettings->psp_api_key);
    }

    /**
     * Create a charge
     *
     * @param Merchant $merchant
     *
     * @return void
     */
    public function charge(array $input): void
    {
        Charge::create([
            'amount' => $input['amount'] * 100, # amount in cents.
            'currency' => 'eur',
            'source' => 'tok_visa',
            'description' => 'test',
        ]);
    }
}
