<?php

namespace App\Http\Services;

use App\Http\Interfaces\Payment;
use App\Http\Traits\JsonResponse;
use App\Models\SuspendedPayPalPayments;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use Sample\PayPalClient;

class PayPalService implements Payment
{
    use JsonResponse;
    public static $PayPalClient;
    public string $orderNumber;

    public function __construct()
    {
        self::setCredentials();
    }

    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }

    public function createOrder($order, $token = null)
    {
        $PayPalRequest = new OrdersCreateRequest();
        $PayPalRequest->prefer('return=representation');
        $PayPalRequest->body = $this->buildRequestBody($order);
        $response = self::$PayPalClient->execute($PayPalRequest);

        try {
            $this->orderNumber = $order->orderNumber;
            $this->insertIntoDB($response);
            //redirect to PayPal to Checkout
            foreach ($response->result->links as $link) {
                if ('approve' === $link->rel) {
                    return $this->response('success, approve this link', 302, $link->href);
                }
            }

            return \false;
        } catch (HttpException $ex) {
            $this->logError($ex->getMessage());

            return \false;
        }
    }

    public function captureOrder($order)
    {
        //validate and authorize
        self::$PayPalClient = PayPalClient::client(self::environment());
        $PayPalRequest = new OrdersCaptureRequest($order);
        $PayPalRequest->prefer('return=representation');

        try {
            return self::$PayPalClient->execute($PayPalRequest);
        } catch (HttpException $ex) {
            $this->logError($ex->getMessage());

            return \false;
        }
    }

    public static function setCredentials()
    {
        self::$PayPalClient = new PayPalHttpClient(self::environment());
    }

    public function insertIntoDB($response)
    {
        //payment not finished yet
        //user is redirected to PayPal website and purchase the return back to route('PayPal.success) on success
        SuspendedPayPalPayments::create([
            'paymentId' => $response->result->id,
            'price' => ($response->result->purchase_units)[0]->amount->value,
            'customerId' => \auth()->guard('api')->id(),
            'customer_email' => $response->result->payer->email_address,
            'phone' => $response->result->payer->phone->phone_number->national_number,
            'orderNumber' => ($response->result->purchase_units)[0]->custom_id,
            'status' => $response->result->status,
            'links' => \json_encode($response->result->links),
            'created_at' => Carbon::now(),
        ]);
    }

    public function logError(string $msg)
    {
        Log::alert($msg);
    }

    public static function environment()
    {
        $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
        $clientSecret = env('PAYPAL_SANDBOX_CLIENT_SECRET');

        return new SandboxEnvironment($clientId, $clientSecret);
    }

    public function buildRequestBody($order)
    {
        return [
            'intent' => 'CAPTURE',
            'description' => $order->orderNumber,
            'payer' => [
                'email_address' => $order->email,
                'name' => [
                    'given_name' => $order->fullName,
                    'surname' => $order->customerId,
                ],
                'phone' => [
                    'phone_type' => 'MOBILE',
                    'phone_number' => [
                        'national_number' => $order->mobile,
                    ],
                ],
                'address_portable' => [
                    'address_line_1' => $order->address,
                    'postal_code' => $order->postal_code,
                ],
            ],
            'application_context' => [
                'return_url' => \route('paypal.success'),
                'cancel_url' => \route('paypal.cancel'),
            ],
            'purchase_units' => [
                0 => [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => intval($order->price),
                    ],
                    'custom_id' => $order->orderNumber,
                ],
            ],
            'shipping_detail' => [
                'name' => $order->shipping,
            ],
        ];
    }
}
