<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponseTrait
{

    /**
     * @param string $message
     * @param array $data
     * @return JsonResponse
     */
    public function ok($message = "success", $data = [], $status = 200): JsonResponse
    {
        $body = [
            'status'  => true,
            'message' => $message
        ];

        if (!empty($data)) {
            $body = array_merge($body, ['data' => $data]);
        };

        return response()->json($body, $status);
    }

    /**
     * @param string $message
     * @param string $error_code
     * @param int $status_code
     * @return JsonResponse
     */
    public function nokTransaction($message, $data, $status_code = 200): JsonResponse
    {
        return response()->json(
            [
                "status"  => true,
                "message" => $message,
                "data"    => $data
            ],
            $status_code
        );
    }


    /**
     * @param string $message
     * @param int $status_code
     * @return JsonResponse
     */
    public function error($message, $status_code = 400): JsonResponse
    {
        return response()->json(
            [
                "status"  => false,
                "message" => $message
            ],
            $status_code
        );
    }
}
