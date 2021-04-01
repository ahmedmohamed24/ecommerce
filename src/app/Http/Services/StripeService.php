<?php

namespace App\Http\Services;

use App\Http\Interfaces\Payment;
use App\Http\Traits\JsonResponse;
use App\Models\Payment as PaymentModel;
use Illuminate\Support\Facades\Log;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeService implements Payment
{
    use JsonResponse;
    private static $stripe;

    public function __construct()
    {
        self::setCredentials();
    }

    public function createOrder($data)
    {
        //authorize
        try {
            //charge
            $response = self::$stripe->charges->create([
                'currency' => $data->currency,
                'amount' => $data->price * 100, //100 cent per dollar
                'source' => 'tok_visa',
                // 'source' => $data->stripeToken,
                'shipping' => [
                    'address' => [
                        'line1' => $data->address,
                        'postal_code' => $data->postal_code,
                    ],
                    'name' => $data->fullName,
                ],
                'description' => 'Stipe pay',
                'receipt_email' => $data->email,
                'metadata' => [
                    'cart_content' => $data->cart_content, //remove
                    'orderNumber' => $data->orderNumber,
                    'customerId' => $data->customerId,
                    'shipping' => $data->shipping,
                    'mobile' => $data->mobile,
                ],
            ]);
            //save into DB
            return $this->insertIntoDB($response);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
        }
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
            $data = self::$stripe->balanceTransactions->all(['limit' => 20]);

            return $this->response('stripe account transactions (only 20)', 200, $data);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
        }
    }

    public function getAllCharges()
    {
        try {
            $response = self::$stripe->charges->all(['limit' => 20]);

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
            $data = self::$stripe->charges->retrieve(
                $charge,
                []
            );

            return $this->response('success', 200, $data);
        } catch (\Exception $e) {
            $this->handleExceptionFromStripe($e);
        }
    }

    public static function setCredentials()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        self::$stripe = new StripeClient(
            env('STRIPE_SECRET')
        );
    }

    public function insertIntoDB($data)
    {
        return PaymentModel::create([
            'charge_id' => $data->id,
            'balance_transaction' => $data->balance_transaction,
            'currency' => $data->currency,
            'amount' => $data->amount,
            'method' => 'stipe',
            'email' => $data->receipt_email,

            'cart_content' => $data->metadata->cart_content,
            'orderNumber' => $data->metadata->orderNumber,
            'customerId' => $data->metadata->customerId,
            'mobile' => $data->metadata->mobile,
            'shipping' => $data->metadata->shipping,

            'name' => $data->shipping->name,
            'address' => $data->shipping->address->line1,
            'postal_code' => $data->shipping->address->postal_code,
            'currency' => $data->currency,
            'description' => $data->description,
        ]);
    }

    public function logError(string $message)
    {
        //log or email or slack notification
        Log::logError($message);

        return \false;
    }

    private function handleExceptionFromStripe(\Throwable $e)
    {
        $exceptionType = \get_class($e);
        if (CardException::class === $exceptionType) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $this->logError('Status is:'.$e->getHttpStatus().'\n');
            $this->logError('Type is:'.$e->getError()->type.'\n');
            $this->logError('Code is:'.$e->getError()->code.'\n');
            $this->logError('Param is:'.$e->getError()->param.'\n');
            $this->logError('Message is:'.$e->getError()->message.'\n');
        } elseif (RateLimitException::class === $exceptionType) {
            // Too many requests made to the API too quickly
            $this->logError('Too many requests made to the API too quickly'.$e->getMessage().'\n');
        } elseif (InvalidRequestException::class === $exceptionType) {
            // Invalid parameters were supplied to Stripe's API
            $this->logError('Invalid parameters were supplied to Stripes API '.$e->getMessage().'\n');
        } elseif (AuthenticationException::class === $exceptionType) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            $this->logError('Authentication with Stripes API failed'.$e->getMessage().'\n');
        } elseif (ApiConnectionException::class === $exceptionType) {
            $this->logError('Network communication with Stripe failed'.$e->getMessage().'\n');
        // Network communication with Stripe failed
        } elseif (ApiErrorException::class === $exceptionType) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $this->logError('General error in stripe api'.$e->getMessage().'\n');
        } else {
            // Something else happened, completely unrelated to Stripe
            $this->logError('Client error'.$e->getMessage().'\n');
        }
    }
}
