<?php

namespace App\Providers;

use App\Clients\EverypayPaymentClient;
use App\Clients\StripePaymentClient;
use App\Contracts\PaymentContract;
use App\Models\Merchant;
use App\Models\PaymentServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const PAYMENT_URI = '/payments/create/';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // check if Uri is payment creation
        if (str_contains($this->app->request->getRequestUri(), self::PAYMENT_URI)) {
            $this->bindPaymentClientToContract();
        }
    }

    /**
     * based on the payment service provider, get the corresponding paymentClient class
     * (e.g. StripePaymentClient, EverypayPaymentClient)
     *
     * @return void
     */
    private function bindPaymentClientToContract(): void
    {
        // get merchant id from uri
        $merchantId = (int) str_replace(self::PAYMENT_URI, '', $this->app->request->getRequestUri());

        // check if merchant id exists
        $merchant = Merchant::findOrFail($merchantId);
        $paymentServiceProvider = $merchant->merchantSettings->paymentServiceProvider->name;

        // based on the payment service provider, get the corresponding paymentClient class
        // (e.g. StripePaymentClient, EverypayPaymentClient)
        $paymentClient = $this->setPaymentClient($paymentServiceProvider);

        // bind paymentClient class to paymentContract Interface
        $this->app->bind(PaymentContract::class, $paymentClient);
    }

    /**
     * Create a payment
     *
     * @param string $paymentServiceProvider
     *
     * @return string
     */
    private function setPaymentClient($paymentServiceProvider): string
    {
        switch ($paymentServiceProvider) {
            case PaymentServiceProvider::STRIPE:
                return StripePaymentClient::class;
            case PaymentServiceProvider::EVERYPAY:
                return EverypayPaymentClient::class;
        }

        throw new \ErrorException("Payment Service Provider {$paymentServiceProvider} does not exist");
    }
}
