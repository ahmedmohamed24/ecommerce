<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    public function requestEmailVerification()
    {
        auth()->user('vendor')->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!', 'data' => []], 302);
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->json(['message' => 'successfully verified', 'data' => []], 302);
    }
}
