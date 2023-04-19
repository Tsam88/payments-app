<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a payment
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createPayment(Request $request, Merchant $merchant)
    {
        // get the payload
        $data = $request->post();

        // create payment
        $response = $this->paymentService->createPayment($data, $request->user(), $merchant);

        return new Response($response, Response::HTTP_OK);
    }
}
