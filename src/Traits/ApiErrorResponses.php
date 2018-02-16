<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Http\JsonResponse;

trait ApiErrorResponses
{
    protected function respondError(
        string $message,
        int $statusCode,
        ?int $errorCode = null,
        array $additionalData = []
    ): JsonResponse {
        $response = [
            'error' => [
                'detail' => $message,
                'status' => (string) $statusCode
            ]
        ];

        if ($errorCode !== null) {
            $response['error']['code'] = (string) $errorCode;
        }

        if (! empty($additionalData)) {
            $response = array_merge($response, $additionalData);
        }

        return response()->json($response, $statusCode);
    }

    protected function respondNotFoundError(
        string $message = 'Not found.',
        ?int $errorCode = null,
        array $additionalData = []
    ): JsonResponse {
        return $this->respondError($message, 404, $errorCode, $additionalData);
    }

    protected function respondInternalServerError(
        string $message = 'An unexpected error occurred on the server.',
        ?int $errorCode = null,
        array $additionalData = []
    ): JsonResponse {
        return $this->respondError($message, 500, $errorCode, $additionalData);
    }
}
