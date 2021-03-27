<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Balance;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\CardException;
use App\Http\Requests\ChargeRequest;
use App\Models\Charge;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\RateLimitException;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\InvalidRequestException;

class CheckoutController extends Controller
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
    public function getBalance()
    {
        try {
            $balance = Balance::retrieve();
            return $this->response('success', 200, $balance);
        } catch (\Throwable $e) {
            $this->handleExceptionFromStripe($e);
        }
    }
    public function getBalanceTransactions()
    {
        try {
            $data = $this->stripe->balanceTransactions->all(['limit' => 20]);
            return $this->response('stripe account transactions (only 20)', 200, $data);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
        }
    }
    public function charge(Request $request)
    {
        //authorize
        try {
            //validate
            $validator = Validator::make($request->only('stripeToken', 'address', 'postal_zip'), [
                'stripeToken' => 'required|string|min:2|max:255',
                'address' => 'required|string|min:2|max:255',
                'postal_zip' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return $this->response('error', 400, $validator->getMessageBag());
            }
            if (Cart::subTotal() <= 0) {
                return $this->response('Please add items to your cart first!', 400, null);
            }
            //charge
            $response = $this->stripe->charges->create([
                'currency' => 'usd',
                'amount' => \intval(Cart::subTotal()) * 100, //100 cent per dollar
                // 'source' => 'tok_visa',
                'source' => $request->stripeToken,
                'shipping' => [
                    'address' => [
                        'line1' => $request->address,
                        'postal_code' => $request->postal_zip
                    ],
                    'name' => \auth()->guard('api')->user()->name,
                ],
                'description' => 'Ecommerce checkout',
                'receipt_email' => \auth()->guard('api')->user()->email,
                'metadata' => [
                    'cart_content' => Cart::content(),
                ],
            ]);
            //save into DB
            Charge::create([
                'charge_id' => $response->id,
                'amount' => $response->amount,
                'receipt_email' => \auth()->guard('api')->user()->email,
                'cart_content' => $response->metadata->cart_content,
                'name' => $response->shipping->name,
                'balance_transaction' => $response->balance_transaction,
                'address' => $response->shipping->address->line1,
                'postal_code' => $response->shipping->address->postal_code,
                'currency' => $response->currency,
                'description' => $response->description,
            ]);
            //destroy cart
            return $this->response('success', 200, $response);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
        }
    }
    public function getAllCharges()
    {
        try {
            $response = $this->stripe->charges->all(['limit' => 20]);
            return $this->response('success', 200, $response);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
            return $this->response('error', 406, $e->getMessage());
        }
    }
    public function getCharge(string $charge)
    {

        try {
            //validate
            $data = $this->stripe->charges->retrieve(
                $charge,
                []
            );
            return $this->response('success', 200, $data);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
        }
    }
    private function handleExceptionFromStripe(\Throwable $e)
    {
        $exceptionType = \get_class($e);
        if ($exceptionType ===  CardException::class) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $this->alert('Status is:' . $e->getHttpStatus() . '\n');
            $this->alert('Type is:' . $e->getError()->type . '\n');
            $this->alert('Code is:' . $e->getError()->code . '\n');
            $this->alert('Param is:' . $e->getError()->param . '\n');
            $this->alert('Message is:' . $e->getError()->message . '\n');
        } elseif ($exceptionType ===  RateLimitException::class) {
            // Too many requests made to the API too quickly
            $this->alert('Too many requests made to the API too quickly' . $e->getMessage() . '\n');
        } elseif ($exceptionType ===  InvalidRequestException::class) {
            // Invalid parameters were supplied to Stripe's API
            $this->alert('Invalid parameters were supplied to Stripes API ' . $e->getMessage() . '\n');
        } elseif ($exceptionType ===  AuthenticationException::class) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            $this->alert('Authentication with Stripes API failed' . $e->getMessage() . '\n');
        } elseif ($exceptionType ===  ApiConnectionException::class) {
            $this->alert('Network communication with Stripe failed' . $e->getMessage() . '\n');
            // Network communication with Stripe failed
        } elseif ($exceptionType ===  ApiErrorException::class) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $this->alert('General error in stripe api' . $e->getMessage() . '\n');
        } else {
            // Something else happened, completely unrelated to Stripe
            $this->alert('Client error' . $e->getMessage() . '\n');
        }
    }
    private function alert(string $msg)
    {
        //log or email or slack notification
        Log::alert($msg);
    }
}
