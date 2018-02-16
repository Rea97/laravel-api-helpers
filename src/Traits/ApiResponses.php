<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\Resource;

trait ApiResponses
{
    use ApiErrorResponses, ApiResponseMessages, ParsesApiQueryParams;

    protected function respondResource(Resource $resource, int $status = 200, array $headers = []): JsonResponse
    {
        return $resource->response()->withHeaders($headers)->setStatusCode($status);
    }

    public function respondJson($data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return response()->json($data, $status, $headers, $options);
    }

    private function updateResponse(array $data): JsonResponse
    {
        $methodName = 'get'.studly_case($data['type'].$data['crud_action']).'Message';

        $response = [
            config('api-helpers.'.$data['response_type'].'.field') => $this->{$methodName}($data['resource'])
        ];

        if (config('api-helpers.messages.with_status_code')) {
            $response = array_merge($response, ['status' => (string) $data['status_code']]);
        }

        if (! empty($data['additional_data'])) {
            $response = array_merge($response, $data['additional_data']);
        }

        return $this->respondJson($response, $data['status_code'], $data['headers']);
    }

    protected function respondSuccessfulDelete(
        string $resource = '',
        int $statusCode = 200,
        array $additionalData = [],
        array $headers = []
    ): JsonResponse {
        return $this->updateResponse([
            'response_type' => 'messages',
            'type' => 'successful',
            'crud_action' => 'delete',
            'resource' => $resource,
            'status_code' => $statusCode,
            'additional_data' => $additionalData,
            'headers' => $headers,
        ]);
    }

    protected function respondFailedDelete(
        string $resource = '',
        int $statusCode = 500,
        array $additionalData = [],
        array $headers = []
    ): JsonResponse {
        return $this->updateResponse([
            'response_type' => 'messages',
            'type' => 'failed',
            'crud_action' => 'delete',
            'resource' => $resource,
            'status_code' => $statusCode,
            'additional_data' => $additionalData,
            'headers' => $headers,
        ]);
    }

    protected function respondSuccessfulUpdate(
        string $resource = '',
        int $statusCode = 200,
        array $additionalData = [],
        array $headers = []
    ): JsonResponse {
        return $this->updateResponse([
            'response_type' => 'messages',
            'type' => 'successful',
            'crud_action' => 'update',
            'resource' => $resource,
            'status_code' => $statusCode,
            'additional_data' => $additionalData,
            'headers' => $headers,
        ]);
    }

    protected function respondFailedUpdate(
        string $resource = '',
        int $statusCode = 500,
        array $additionalData = [],
        array $headers = []
    ): JsonResponse {
        return $this->updateResponse([
            'response_type' => 'errors',
            'type' => 'failed',
            'crud_action' => 'update',
            'resource' => $resource,
            'status_code' => $statusCode,
            'additional_data' => $additionalData,
            'headers' => $headers,
        ]);
    }
}
