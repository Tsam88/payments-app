<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\PaymentContract;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\User;
use App\Validators\PaymentValidation;

class PaymentService
{
    /**
     * @var PaymentValidation
     */
    private $paymentValidation;
    /**
     * @var PaymentContract
     */
    private $paymentContract;

    public function __construct(PaymentValidation $paymentValidation, PaymentContract $paymentContract)
    {
        $this->paymentValidation = $paymentValidation;
        $this->paymentContract = $paymentContract;
    }

    /**
     * Create a payment
     *
     * @param array     $input
     * @param User      $user
     * @param Merchant  $merchant
     *
     * @return array
     */
    public function createPayment(array $input, User $user, Merchant $merchant): void
    {
        // data validation
        $data = $this->paymentValidation->createPayment($input);

        try {
            // create a payment on corresponding payment client
            $this->paymentContract->createPayment($data, $user, $merchant);

            // save payment
            $this->savePaymentInformation($input, $user, $merchant);
        } catch (\Exception $e) {
            // something went wrong, throw exception
            throw $e;
        }
    }

    /**
     * Save payment information
     *
     * @param array     $input
     * @param User      $user
     * @param Merchant  $merchant
     *
     * @return array
     */
    private function savePaymentInformation(array $input, User $user, Merchant $merchant): void
    {
        Payment::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'payment_service_provider_id' => $merchant->merchantSettings->payment_service_provider_id,
            'amount' => $input['amount'],
            'last4' => substr($input['card']['card_number'], -4),
            'expiration_date' => $input['card']['expiration_date'],
            'cardholder_name' => $input['card']['cardholder_name'],
        ]);
    }
}
