<?php

namespace App\Http\Services;

use App\Http\Interfaces\Payment;
use App\Http\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use Sample\PayPalClient;
use Stripe\Order;

class PayPalService implements Payment
{
    use JsonResponse;
    public static $paypablClient;

    public function __construct()
    {
        self::setCredentials();
    }

    public function createOrder(Request $request)
    {
        $paypalRequest = new OrdersCreateRequest();
        $paypalRequest->prefer('return=representation');
        $paypalRequest->body = self::buildRequestBody($request);
        // self::$paypablClient = PayPalClient::client(self::environment());

        try {
            $response = self::$paypablClient->execute($paypalRequest);
            $this->insertIntoDB($request);

            return $this->response('success', 200, $response);
        } catch (HttpException $ex) {
            $this->logError($ex->getMessage());

            return $this->response('failed', 500);
        }
    }

    public function captureOrder(Request $request)
    {
        //validate and authorize
        self::$paypablClient = PayPalClient::client(self::environment());
        $paypalRequest = new OrdersCaptureRequest($request->order);
        $paypalRequest->prefer('return=representation');

        try {
            $response = self::$paypablClient->execute($paypalRequest);

            return $this->response('success', 200, $response);
        } catch (HttpException $ex) {
            $this->logError($ex->getMessage());

            return $this->response('failed', 500);
        }
    }

    public static function setCredentials()
    {
        $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
        $clientSecret = env('PAYPAL_SANDBOX_CLIENT_SECRET');

        $environment = new SandboxEnvironment($clientId, $clientSecret);
        self::$paypablClient = new PayPalHttpClient($environment);
    }

    public function insertIntoDB($data)
    {
        Order::create([
            'charge_id' => $data->id,
            'amount' => $data->amount,
            'receipt_email' => \auth()->guard('api')->user()->email,
            'cart_content' => $data->metadata->cart_content,
            'name' => $data->shipping->name,
            'balance_transaction' => $data->balance_transaction,
            'address' => $data->shipping->address->line1,
            'postal_code' => $data->shipping->address->postal_code,
            'currency' => $data->currency,
            'description' => $data->description,
        ]);
    }

    public function logError(string $msg)
    {
        Log::logError($msg);
    }

    private static function buildRequestBody(Request $request)
    {
        return [
            'intent' => 'CAPTURE',
            'application_context' => [
                'brand_name' => 'EXAMPLE INC',
                'locale' => 'en-US',
                'landing_page' => 'BILLING',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'PAY_NOW',
                'return_url' => \route('paypal.success'),
                'cancel_url' => \route('paypal.cancel'),
            ],
            'purchase_units' => [
                0 => [
                    'reference_id' => 'PUHF',
                    'soft_descriptor' => 'HighFashions',
                    'description' => 'Sporting Goods',
                    'custom_id' => 'CUST-HighFashions',
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '220.00',
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'USD',
                                'value' => '180.00',
                            ],
                            'shipping' => [
                                'currency_code' => 'USD',
                                'value' => '20.00',
                            ],
                            'handling' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'tax_total' => [
                                'currency_code' => 'USD',
                                'value' => '20.00',
                            ],
                            'shipping_discount' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                        ],
                    ],
                    'items' => [
                        0 => [
                            'name' => 'T-Shirt',
                            'description' => 'Green XL',
                            'sku' => 'sku01',
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => '90.00',
                            ],
                            'tax' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'quantity' => '1',
                            'category' => 'PHYSICAL_GOODS',
                        ],
                        1 => [
                            'name' => 'Shoes',
                            'description' => 'Running, Size 10.5',
                            'sku' => 'sku02',
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => '45.00',
                            ],
                            'tax' => [
                                'currency_code' => 'USD',
                                'value' => '5.00',
                            ],
                            'quantity' => '2',
                            'category' => 'PHYSICAL_GOODS',
                        ],
                    ],
                    'shipping' => [
                        'method' => 'United States Postal Service',
                        'address' => [
                            'address_line_1' => '123 Townsend St',
                            'address_line_2' => 'Floor 6',
                            'admin_area_2' => 'San Francisco',
                            'admin_area_1' => 'CA',
                            'postal_code' => '94107',
                            'country_code' => 'US',
                        ],
                    ],
                ],
            ],
        ];
    }
}
