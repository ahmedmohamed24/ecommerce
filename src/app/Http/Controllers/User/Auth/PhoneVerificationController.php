<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Services\TwillioService;
use App\Http\Traits\JsonResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhoneVerificationController extends Controller
{
    use JsonResponse;

    public function attachPhone(Request $request)
    {
        $user = User::findOrFail(\auth()->id());
        //check if phone is already verified
        if ($user->phone_verified_at) {
            return $this->response('Email is already verified.', 400);
        }
        //check if otp is sent since only one minute
        $data = DB::table('sms_verifications')->whereDate('created_at', '<=', Carbon::now()->subMinute()->toDateString())->count();
        if ($data > 0) {
            return $this->response('please wait one minute berfore requesting another OTP.', 400);
        }
        //add phone number and send otp to the user
        $user->update([
            'phone' => $request->phone,
        ]);
        $sms = new TwillioService();
        $otp = rand(100000, 999999);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth()->user()->email,
            'phone_number' => $request->phone,
            'otp' => $otp,
            'created_at' => Carbon::now(),
        ]);
        $sms->send($request->phone, $otp);

        return $this->response('please verify your phone number', 302);
    }
}
