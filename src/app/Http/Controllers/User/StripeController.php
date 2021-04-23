<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\StripeService;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    private StripeService $stripe;

    public function __construct(StripeService $stripe)
    {
        $this->stripe = $stripe;
    }

    public function createOrder(Request $request)
    {
        return $this->stripe->createOrder($request);
    }

    public function getBalance()
    {
        return $this->stripe->getBalance();
    }

    public function getBalanceTransactions()
    {
        return $this->stripe->getBalanceTransactions();
    }

    public function getAllCharges()
    {
        return $this->stripe->getAllCharges();
    }
}
