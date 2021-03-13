<?php
namespace App\Http\Traits;

trait JsonResponse
{
    public function response(string $message, int $status, $data=[])
    {
        return \response()->json([
            'message'=>$message,
            'data'=>$data
        ], $status);
    }
}
