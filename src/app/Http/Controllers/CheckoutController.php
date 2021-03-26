<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;

class CheckoutController extends Controller
{
    use JsonResponse;

    private function auth()
    {
        return Auth::guard('api')->user();
    }
    public function generatePaymentMethod(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
            \env('STRIPE_SECRET')
        );
        $this->auth()->createOrGetStripeCustomer();
        $paymentMethod = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
                "number"    => $request->card_number,
                "exp_month" => $request->exp_month,
                "exp_year"  => $request->exp_year,
                "cvc"       => $request->cvc,
            ],
            'metadata' => [
                'id'   => $this->auth()->id,
                'name' => $this->auth()->name,
                'email' => $this->auth()->email,
            ]
        ]);
        $this->auth()->addPaymentMethod($paymentMethod);
        return $this->response('success', 201, $paymentMethod);
    }
    public function charge(Request $request)
    {
        try {
            $amount = Cart::subtotal(2, '.', '');
            if ($amount <= 0) {
                return $this->response('Please Add items to Cart!', Response::HTTP_EXPECTATION_FAILED, );
            }
            $checkout = $this->auth()->charge(\round($amount), $request->paymentMethodId, [
                'customer' => $this->auth()->email,
                'description' => $request->description,
            ]);
            // $checkout=Stripe::charges()->create([
            //     'amount'=> $amount,
            //     'currency'=>'usd',
            //     'source'=>$request->paymentMethodId,
            //     'description'=>$request->description,
            //     'receipt_email'=>$this->auth()->email(),
            //     'meta_data'=>[]
            // ]);
            $data = [
                'checkout_id' => $checkout->id,
                'customer' => $checkout->customer,
                'description' => $checkout->description,
                'payment_method' => $checkout->payment_method,
                'payment_method_details' => $checkout->payment_method_details,
                'receipt_url' => $checkout->receipt_url,
                'status' => $checkout->status,
                'amount' => $checkout->amount,
                'balance_transaction' => $checkout->balance_transaction,
                'user_id' => $this->auth()->id,
                'user_name' => $this->auth()->name,
                'user_email' => $this->auth()->email,
            ];
            //empty cart
            Cart::destroy();
            return $this->response('success', 200, $data);
        } catch (\Throwable $th) {
            return $this->response('some thing went wrong, please try again later', 500, $th);
        }
    }
}
