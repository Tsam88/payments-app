<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\Models\Merchant;
use App\Models\User;

interface PaymentContract
{
    public function setApiKey(Merchant $merchant);

    public function charge(array $input);

    public function createPayment(array $input, User $user, Merchant $merchant);
}
