<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Services\PayPalService;
use App\Http\Services\StripeService;
use App\Http\Traits\JsonResponse;
use App\Models\Order;
use App\Models\Payment;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Log;
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
            'paid' => \false,
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
            $order = Order::where('orderNumber', $orderNumber)->where('customerId', \auth()->guard('api')->id())->where('paid', false)->firstOrFail();

            switch ($order->paymentMethod) {
                case 'stripe':
                    if (!$request->stipeToken) {
                        return $this->response('Token not specified', 400);
                    }
                    $response = (new StripeService())->createOrder($order);
                    if (!$response) { // service returns false on error and throw error in log file
                        return $this->internalErrorResponse();
                    }
                    //token is provided and purchasing is done
                    $order->update([
                        'paid' => \true,
                    ]);

                    return $this->response('successfully paid!', 200);

                default:
                    //PayPal

                    $response = (new PayPalService())->createOrder($order);
                    if (!$response) { // service returns false on error and throw error in log file
                        return $this->internalErrorResponse();
                    }

                    //redirect to PayPal to Checkout
                    foreach ($response->result->links as $link) {
                        if ('approve' === $link->rel) {
                            return $this->response('success, approve this linke', 302, $link->href);
                        }
                    }

                    return $this->internalErrorResponse();
            }
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function paypalOrderSuccess()
    {
        //authorize
        // SusbendedPayPalPayments::where('customerId', \auth()->guard('api')->id())->firstOrFail();
        //capture order here
        $token = $_GET['token'];

        try {
            $response = (new PayPalService())->captureOrder($token);
            if (!$response) {
                return $this->response('This request has been captured before!', 400);
            }
            Payment::create([
                'charge_id' => $response->result->id,
                'balance_transaction' => null,
                'currency' => (($response->result->purchase_units)[0])->amount->currency_code,
                'amount' => (($response->result->purchase_units)[0])->amount->value,
                'method' => 'paypal',
                'name' => $response->result->payer->name->given_name.$response->result->payer->name->surname,
                'email' => $response->result->payer->email_address,
                'mobile' => \null,
                'shipping' => 'Armada',
                'address' => (($response->result->purchase_units)[0])->shipping->address->address_line_1.(($response->result->purchase_units)[0])->shipping->address->address_line_2,
                'postal_code' => (($response->result->purchase_units)[0])->shipping->address->postal_code,
                'orderNumber' => (($response->result->purchase_units)[0])->custom_id,
                'customerId' => null,
                'description' => null,
            ]);
            Order::where('orderNumber', (($response->result->purchase_units)[0])->custom_id)->update(['paid' => \true]);

            return $this->response('success', 200, null);
        } catch (\Throwable $th) {
            Log::alert($th);

            return $this->internalErrorResponse();
        }
    }

    public function paypalOrderCancelled()
    {
        return $this->response('Payment cancelled! redirect to home page', 302, \null);
    }
}
