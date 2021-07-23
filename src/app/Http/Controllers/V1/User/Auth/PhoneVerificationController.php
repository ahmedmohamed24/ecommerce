<?php

namespace App\Http\Controllers\V1\User\Auth;

use App\Events\UserAttachPhoneEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\PhoneRequest;
use App\Http\Requests\Vendor\PhoneVerificationRequest;
use App\Http\Traits\JsonResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PhoneVerificationController extends Controller
{
    use JsonResponse;

    public function attachPhone(PhoneRequest $request)
    {
        $user = User::findOrFail(\auth()->id());
        //check if phone is already verified
        if ($user->phone_verified_at) {
            return $this->response('Email is already verified.', 400);
        }
        //check if otp is sent since only one minute
        $data = DB::table('sms_verifications')->whereDate('created_at', '<=', Carbon::now()->subMinute()->toDateString())->count();
        if ($data > 0) {
            return $this->response('please wait one minute before requesting another OTP.', 400);
        }
        // fire the event to add row to DB and send msg (sms)
        $otp = rand(100000, 999999);
        \event(new UserAttachPhoneEvent($request->phone, $otp));

        $user->update([
            'phone' => $request->phone,
        ]);

        return $this->response('please verify your phone number', 302);
    }

    public function verify(PhoneVerificationRequest $request)
    {
        $isDataValid = DB::table('sms_verifications')
            ->where('user_email', \auth()->user()->email)
            ->where('otp', $request->otp)
            ->where('phone_number', $request->phone)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->count()
        ;

        if (0 === $isDataValid) {
            return $this->response('These credentials does not match our records', 406);
        }
        auth()->user()->update([
            'phone_verified_at' => Carbon::now(),
        ]);
        DB::table('sms_verifications')->where('user_email', \auth()->user()->email)->delete();

        return $this->response('successfully verified', 200);
    }
}
