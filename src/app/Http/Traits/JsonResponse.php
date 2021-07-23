<?php

namespace App\Http\Traits;

trait JsonResponse
{
    public function response(string $message, int $status, $data = [])
    {
        return \response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function notFoundReturn(\Throwable $th)
    {
        return \response()->json([
            'message' => 'Not Found',
            'data' => $th->getMessage(),
        ], 404);
    }

    public function internalErrorResponse()
    {
        return \response()->json([
            'message' => 'internal server error, please try again later!',
            'data' => null,
        ], 500);
    }
}
