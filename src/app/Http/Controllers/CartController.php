<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Balance;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use App\Http\Traits\JsonResponse;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException;

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
<<<<<<< HEAD
        $validator=Validator::make($request->all(), [
            // 'id'=>'required|numeric|exists:products,slug',
            'slug'=>'required|string|exists:products,slug',
            'quantity'=>'required|numeric',
=======
        $validator = Validator::make($product->all(), [
            'id' => 'required|numeric|exists:products,id',
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|numeric',
            'price' => 'required|numeric'
>>>>>>> ahmed_wip_stripe_another
        ]);
        if ($validator->fails()) {
            return $this->response('error', 406, $validator->getMessageBag());
        }
<<<<<<< HEAD
        try {
            //presist
            $product=Product::where('slug', $request->slug)->firstOrFail();
            $cartItem=Cart::add(['id' => $product->slug, 'name' => $product->name, 'qty' => $request->quantity ?? 1, 'price' =>$product->price]);
            //response
            return $this->response('success', 200, $cartItem);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
=======
        //presist
        $cartItem = Cart::add(['id' => $product->id, 'name' => $product->name, 'qty' => $product->quantity ?? 1, 'price' => $product->price]);
        // ->associate('Product');
        //response
        return $this->response('success', 200, $cartItem);
>>>>>>> ahmed_wip_stripe_another
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
