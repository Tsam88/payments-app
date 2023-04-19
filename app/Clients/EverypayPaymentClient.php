<?php

declare(strict_types = 1);

namespace App\Clients;

use App\Exceptions\EverypayException;
use App\Models\Merchant;
use App\Models\User;
use Everypay\Everypay;
use Everypay\Payment;

class EverypayPaymentClient extends PaymentClient
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

        /**
         * Set this true to test your sandbox account (also provide your sandbox secret API key above).
         * Ommit it or set it false to actually use your live account (also provide your live secret API key above
         * - but be carefull, this is no longer a test!).
         */
        Everypay::$isTest = true;

        // create a charge
        $this->charge($input);

        var_dump('EVERYPAY');
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
        /**
         * Either your live secret API key or your sandbox secret API key.
         */
        Everypay::setApiKey($merchant->merchantSettings->psp_api_key);
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
        /**
         * You can only handle card information if you have PCI DSS SEQ D
         * This example is only for MOTO (mail order telephone order) payments
         * You need to use this plugin with our iFrame solution in order to handle card data
         */
        $expirationDate = explode('/', $input['card']['expiration_date']);
        $expirationMonth = $expirationDate[0];
        $expirationYear = $expirationDate[1];

        $params = array(
            'card_number'       => (string) $input['card']['card_number'],
            'expiration_month'  => $expirationMonth,
            'expiration_year'   => $expirationYear,
            'cvv'               => (string) $input['card']['cvv'],
            'holder_name'       => $input['card']['cardholder_name'],
            'amount'            => $input['amount'] * 100 # amount in cents.
        );

        $response = Payment::create($params);

        // something went wrong, throw exception
        if (isset($response->error)) {
            throw new EverypayException($response->error->message);
        }
    }
}
