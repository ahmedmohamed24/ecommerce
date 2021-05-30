<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Services\PayPalService;
use App\Http\Traits\JsonResponse;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Str;

class OrderController extends Controller
{
    use JsonResponse;

    public function createOrder(OrderRequest $request)
    {
        $price = $this->calculatePrice($request->cart);
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
            'price' => $price,
            'currency' => 'usd',
            'status' => 'not paid',
            'cart_content' => \json_encode($request->cart),
        ]);
        //redirect to checkout
        return $this->response('redirect to checkout', 302, $order);
    }

    public function calculatePrice(array $cart)
    {
        $products = [];
        foreach ($cart as $item) {
            \array_push($products, $item['product']);
        }

        return Product::whereIn('slug', $products)->sum('price');
    }

    public function checkout(CheckoutRequest $request, string $orderNumber)
    {
        try {
            $order = Order::where('orderNumber', $orderNumber)->where('customerId', \auth()->guard('api')->id())->where('paid', false)->firstOrFail();

            $paymentService = \config('payment.'.$order->paymentMethod);
            $response = $paymentService->createOrder($order, $request->stripeToken); //in PayPal token would be null
            if (!$response) { // service returns false on error and throw error in log file
                return $this->internalErrorResponse();
            }

            return $response;
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function PayPalOrderSuccess(Request $request)
    {
        //authorize
        // SuspendedPayPalPayments::where('customerId', \auth()->guard('api')->id())->firstOrFail();
        //capture order here
        $token = $request->token;

        try {
            $response = (new PayPalService())->captureOrder($token);
            if (!$response) {
                return $this->response('This request has been captured before!', 406);
            }
            //check if saved before
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

    public function PayPalOrderCancelled()
    {
        return $this->response('Payment cancelled! redirect to home page', 302, \null);
    }
}
