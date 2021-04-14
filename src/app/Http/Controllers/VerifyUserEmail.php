<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyUserEmail extends Controller
{
    public function requestEmailVerification()
    {
        auth()->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!', 'data' => []], 302);
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->json(['message' => 'successfully verified', 'data' => []], 302);
    }
}
