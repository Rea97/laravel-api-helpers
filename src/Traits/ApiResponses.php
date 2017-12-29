<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\Resource;

trait ApiResponses
{
    use ApiResponseMessages, ParsesApiQueryParams;

    protected function respondResource(Resource $resource, int $status = 200, array $headers = []): JsonResponse
    {
        return $resource->response()->withHeaders($headers)->setStatusCode($status);
    }

    public function respondJson($data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return response()->json($data, $status, $headers, $options);
    }

    protected function respondSuccessfulUpdate(string $resource = ''): JsonResponse
    {
        /**
         * With this change, the developer will be able to add sibling data to the 'message' key
        return $this->respondJson(
        array_merge(['message' => $this->getSuccessfulUpdateMessage($resource)], $additionalData)
        );
         */
        // TODO: put key names in a config file, with this, the developer will be able to change the 'message key dynamically'
        return $this->respondJson(['message' => $this->getSuccessfulUpdateMessage($resource)]);
    }

    protected function respondFailedUpdate(string $resource = ''): JsonResponse
    {
        return $this->respondJson(['message' => $this->getFailedUpdateMessage($resource)]);
    }
}
