<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Services\PayPalService;
use App\Http\Services\StripeService;
use App\Http\Traits\JsonResponse;
use App\Models\Order;
use Gloudemans\Shoppingcart\Facades\Cart;
use Str;

class OrderController extends Controller
{
    use JsonResponse;

    public function createOrder(OrderRequest $request)
    {
        //authorize
        if (Cart::count() < 1) {
            return $this->response('Cart is Empty', 400);
        }
        //save cart content in db in orders table
        $order = Order::create([
            'orderNumber' => Str::uuid()->toString(),
            'customerId' => \auth()->guard('api')->id(),
            'email' => \auth()->guard('api')->user()->email,
            'shipping' => $request->shipping,
            'paymentMethod' => $request->paymentMethod,
            'baid' => \false,
            'fullName' => $request->fullName,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'price' => Cart::subtotal(),
            'currency' => 'usd',
            'status' => 'not paid',
            'cart_content' => Cart::content(),
        ]);
        //redirect to checkout
        return $this->response('redirect to checkout', 302, $order);
    }

    public function checkout(CheckoutRequest $request, string $orderNumber)
    {
        try {
            $order = Order::where('orderNumber', $orderNumber)->where('customerId', \auth()->guard('api')->id())->where('baid', false)->firstOrFail();

            switch ($order->payment) {
                case 'stripe':
                    if (!$request->stipeToken) {
                        return $this->response('Token not specified', 400);
                    }
                    $response = (new StripeService())->createOrder($order);

                    break;

                default:
                    //paypal
                    $response = (new PayPalService())->createOrder($order);
            }
            if (!$response) {// service returns false on error and throw error in log file
                return $this->internalErrorResponse();
            }
            if ('stripe' === $order->payment) {
                //token is provided and pruchasing is done
                $order->update([
                    'baid' => \true,
                ]);
            }

            return $this->response('success', 200, $response);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function paypalOrderSuccess()
    {
        //capture order here
        $token = $_GET['token'];
        $payerId = $_GET['PayerID'];
    }
}
