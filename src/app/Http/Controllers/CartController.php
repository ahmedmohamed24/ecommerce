<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\JsonResponse;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException;

class CartController extends Controller
{
    use JsonResponse;
    public function content()
    {
        return $this->response('success', 200, ['items'=>Cart::content(),'sub total'=>Cart::subtotal()]);
    }
    public function store(Request $request)
    {
        //validate
        $validator=Validator::make($request->all(), [
            // 'id'=>'required|numeric|exists:products,slug',
            'slug'=>'required|string|exists:products,slug',
            'quantity'=>'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->response('error', 406, $validator->getMessageBag());
        }
        try {
            //presist
            $product=Product::where('slug', $request->slug)->firstOrFail();
            $cartItem=Cart::add(['id' => $product->slug, 'name' => $product->name, 'qty' => $request->quantity ?? 1, 'price' =>$product->price]);
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
