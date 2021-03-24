<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\JsonResponse;
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
    public function store(Request $product)
    {
        //validate
        $validator=Validator::make($product->all(), [
            'id'=>'required|numeric|exists:products,id',
            'name'=>'required|string|max:255',
            'quantity'=>'nullable|numeric',
            'price'=>'required|numeric'
        ]);
        if ($validator->fails()) {
            return $this->response('error', 406, $validator->getMessageBag());
        }
        //presist
        $cartItem=Cart::add(['id' => $product->id, 'name' => $product->name, 'qty' => $product->quantity ?? 1, 'price' =>$product->price]);
        // ->associate('Product');
        //response
        return $this->response('success', 200, $cartItem);
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
