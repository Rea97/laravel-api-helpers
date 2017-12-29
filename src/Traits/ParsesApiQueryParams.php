<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Http\Request;

trait ParsesApiQueryParams
{
    /**
     * Parses a parameter list separated by a given delimiter.
     */
    protected function parseApiListParameters(string $param, string $delimiter, Request $request): array
    {
        return $request->filled($param) ? explode($delimiter, $request->query($param)) : [];
    }

    /**
     * Parses the "with" query string parameter from the request.
     */
    protected function parseWithParameter(Request $request): array
    {
        return $this->parseApiListParameters('with', ',', $request);
    }

    /**
     * Parses the "where" query string and returns a criteria array.
     *
     * @param Request $request
     * @param array   $attributes Model attributes that are searchable
     *
     * @return array
     */
    protected function parseWhereParameter(Request $request, array $attributes): array
    {
        $criteria = [];
        $wheres = $this->parseApiListParameters('where', ',', $request);

        foreach ($wheres as $where) {
            [$field, $value] = explode(':', $where);

            // We need to verify that the requested field exists in the model
            if (in_array($field, $attributes, true)) {
                switch ($value) {
                    case 'null':
                        $value = null;
                        break;
                    case 'true':
                        $value = true;
                        break;
                    case 'false':
                        $value = false;
                        break;
                }
                $criteria[$field] = $value;
            }
        }

        return $criteria;
    }

    /**
     * Parses all the query parameters available in the package.
     */
    protected function parseQueryParameters(Request $request, array $attributes): array
    {
        return [
            $this->parseWithParameter($request),
            $this->parseWhereParameter($request, $attributes)
        ];
    }
}
