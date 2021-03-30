<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\Payment;
use App\Http\Requests\OrderRequest;
use App\Http\Services\PayPalService;
use App\Http\Services\StripeService;
use App\Http\Traits\JsonResponse;

class OrderController extends Controller
{
    use JsonResponse;

    private $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function order(OrderRequest $request)
    {
        switch ($request->payment) {
            case 'paypal':
                $this->payment = new PayPalService();

                break;

            default:
                $this->payment = new StripeService();

                break;
        }
        $this->payment->createOrder($request);
    }

    public function paypalOrderSuccess(string $token)
    {
        //capture order here
    }
}
