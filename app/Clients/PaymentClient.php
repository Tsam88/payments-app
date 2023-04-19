<?php

declare(strict_types = 1);

namespace App\Clients;

use App\Contracts\PaymentContract;
use App\Models\Merchant;
use App\Models\User;

class PaymentClient implements PaymentContract
{
    /**
     * Set api key
     *
     * @param Merchant $merchant
     *
     * @return void
     */
    public function setApiKey(Merchant $merchant): void
    {
        //
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
        //
    }

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
        //
    }
}
