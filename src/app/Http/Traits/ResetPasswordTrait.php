<?php

namespace App\Http\Traits;

use App\Jobs\ResetPasswordJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait ResetPasswordTrait
{
    use JsonResponse;
    public $tableName = '';
    public $model = '';
    public $guard = '';

    public function sendTokenViaEmail(Request $request)
    {
        $validator = Validator::make($request->only('email'), ['email' => "required|email|exists:{$this->tableName},email"]);
        if ($validator->fails()) {
            return $this->response('fail', Response::HTTP_FORBIDDEN, $validator->getMessageBag());
        }
        if ($this->getResetRecord($request->email)) {
            return $this->response('Email has been sent, please wait 5 minutes and try again', Response::HTTP_FORBIDDEN);
        }
        //create token and save it to database and send email
        $token = Str::random(50);
        $this->saveToDB($token, $request->email);
        $this->sendEmail($token, $request->email);

        return $this->response('success', Response::HTTP_OK);
    }

    public function saveToDB(string $token, string $email)
    {
        DB::table('password_resets')->insert(['token' => $token, 'email' => $email, 'created_at' => Carbon::now()]);
    }

    public function sendEmail(string $token, string $email)
    {
        dispatch(new ResetPasswordJob($token, $email, $this->guard));
    }

    public function createNewPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
            'email' => 'required|email',
            'token' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->response('fail', Response::HTTP_FORBIDDEN, $validator->getMessageBag());
        }
        //check if the given data is true and only 5 minutes ago
        $dbResponse = DB::table('password_resets')->where('email', $request->email)->where('token', $request->token)->where('created_at', '>=', Carbon::now()->subMinutes(5))->select()->latest()->first();
        if (empty($dbResponse)) {
            return $this->response('fail, these credentials does not match our records', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->model::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

            return $this->response('successfully reset, please login again', Response::HTTP_ACCEPTED);
        } catch (\Throwable $th) {
            //throw it logs and return error message
            return $this->response('fails', Response::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    protected function getResetRecord($email)
    {
        return DB::table('password_resets')->where('email', $email)->where('created_at', '>=', Carbon::now()->subMinutes(5))->select()->latest()->first();
    }
}
