<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\PaymentServiceProvider;

class PSPService
{
    /**
     * Create a payment
     *
     * @param int $pspId
     *
     * @return array
     */
    public function getPaymentServiceProviderById(int $pspId): PaymentServiceProvider
    {
        $psp = PaymentServiceProvider::findOrFail($pspId);

        return $psp;
    }
}
