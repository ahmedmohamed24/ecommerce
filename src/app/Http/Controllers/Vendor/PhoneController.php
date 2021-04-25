<?php

namespace App\Http\Controllers\Vendor;

use App\Events\VendorAddedPhoneEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\PhoneRequest;
use App\Http\Requests\Vendor\PhoneVerificationRequest;
use App\Http\Traits\JsonResponse;
use App\Models\Vendor;
use Carbon\Carbon;
use DB;

class PhoneController extends Controller
{
    use JsonResponse;

    public function store(PhoneRequest $request)
    {
        if (Vendor::findOrFail(\auth('vendor')->id())->phone_verified_at) {
            return $this->response('Your phone is already verified.', 406);
        }
        //validation
        $otp = rand(100000, 999999);
        event(new VendorAddedPhoneEvent($request->phone, $otp));
        \auth('vendor')->user()->update([
            'phone' => $request->phone,
        ]);

        return $this->response('phone successfully added.', 200);
    }

    public function verify(PhoneVerificationRequest $request)
    {
        $isDataValid = DB::table('sms_verifications')
            ->where('user_email', \auth('vendor')->user()->email)
            ->where('otp', $request->otp)
            ->where('phone_number', $request->phone)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->count()
        ;

        if (0 === $isDataValid) {
            return $this->response('These credentials does not match our records', 406);
        }
        auth('vendor')->user()->update([
            'phone_verified_at' => Carbon::now(),
        ]);
        DB::table('sms_verifications')->where('user_email', \auth('vendor')->user()->email)->delete();

        return $this->response('phone successfully verified.', 200);
    }
}
