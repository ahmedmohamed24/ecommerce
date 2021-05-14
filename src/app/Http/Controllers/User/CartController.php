<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Models\Product;
use Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Balance;
use Stripe\Stripe;
use Stripe\StripeClient;

class CartController extends Controller
{
    use JsonResponse;
    private $stripe;

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $this->stripe = new StripeClient(
            env('STRIPE_SECRET')
        );
    }

    public function content()
    {
        return $this->response('success', 200, ['items' => Cart::content(), 'sub total' => Cart::subtotal()]);
    }

    public function getBalance()
    {
        $balance = Balance::retrieve();

        return $this->response('success', 200, $balance);
    }

    public function getBalanceTransactions()
    {
        $data = $this->stripe->balanceTransactions->all(['limit' => 20]);

        return $this->response('stripe account transactions (only 20)', 200, $data);
    }

    public function store(Request $request)
    {
        //validate
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|exists:products,slug',
            'quantity' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return $this->response('error', 406, $validator->getMessageBag());
        }

        try {
            $product = Product::where('slug', $request->slug)->firstOrFail();
            $cartItem = Cart::add(['id' => $product->slug, 'name' => $product->name, 'qty' => $request->quantity ?? 1, 'price' => $product->price]);
            //response
            return $this->response('success', 200, $cartItem);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function empty()
    {
        Cart::destroy();

        return $this->response('success', 200, null);
    }

    public function remove(Request $request)
    {
        try {
            Cart::remove($request->rowId);

            return $this->response('success', 200, null);
        } catch (InvalidRowIDException $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function count()
    {
        return $this->response('succes', 200, Cart::count());
    }
}
