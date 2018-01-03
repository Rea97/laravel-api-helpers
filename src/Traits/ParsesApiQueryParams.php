<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Http\Request;
use ReaDev\ApiHelpers\Exceptions\RelationNotFoundException;

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
    protected function parseWithParameter(Request $request, array $relations): array
    {
        $includes = $this->parseApiListParameters('with', ',', $request);

        foreach($includes as $include) {
            if (! in_array($include, $relations, true)) {
                throw new RelationNotFoundException("Relation '{$include}' does not exists in the resource.");
            }
        }

        return $includes;
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
            [$field, $operator, $value] = $this->structurewhereArray($where);

            // We need to verify that the requested field exists in the model
            if (in_array($field, $attributes, true)) {
                $criteria[] = [$field, $operator, $this->castIfBoolOrNull($value)];
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

    /**
     * Parses a string and build an array with 3 items [$field, $operator, $value]
     */
    private function structurewhereArray(string $where): array
    {
        $delimiter = ':';
        $operator = '=';

        if (str_contains($where, '<')) {
            $delimiter = '<';
            $operator = '<';
        }

        if (str_contains($where, '>')) {
            $delimiter = '>';
            $operator = '>';
        }

        if (str_contains($where, '<:')) {
            $delimiter = '<:';
            $operator = '<=';
        }

        if (str_contains($where, '>:')) {
            $delimiter = '>:';
            $operator = '>=';
        }

        [$field, $value] = explode($delimiter, $where);


        return [$field, $operator, $value];
    }

    /**
     * Takes an string and checks if it matches with "null", "true" or "false" and then makes a "cast".
     *
     * @param string $value
     *
     * @return mixed
     */
    private function castIfBoolOrNull(string $value)
    {
        switch ($value) {
            case 'null':
                return null;
            case 'true':
                return true;
                break;
            case 'false':
                return false;
            default:
                return $value;
        }
    }
}
