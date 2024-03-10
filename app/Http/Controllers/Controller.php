<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendSuccessResponse($data, $message = "success", $code = 200): JsonResponse
    {
        return response()->json([
            "message" => $message,
            "data" => $data
        ], $code);
    }

    public function sendErrorResponse($message = "error", $code = 400): JsonResponse
    {
        return response()->json([
            "message" => $message,
        ], $code);
    }
}
