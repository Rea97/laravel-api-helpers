<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Http\Request;
use ReaDev\ApiHelpers\Exceptions\AttributeNotFoundException;
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
            [$field, $operator, $value] = $this->structureWhereFromString($where);

            // We need to verify that the requested field exists in the model
            if (! in_array($field, $attributes, true)) {
                throw new AttributeNotFoundException("Field '{$col}' is not available in the requested resource.");
            }

            $criteria[] = [$field, $operator, $this->castIfBoolOrNull($value)];
        }

        return $criteria;
    }

    /**
     * Parses the "sort" query string and returns an array of arrays with the indexes "sort_col" and "sort_direction".
     *
     * @param Request $request
     * @param array   $attributes Model attributes that are searchable
     * @param array   $default    Default returned if sort query string has not value
     *
     * @return array
     * @throws AttributeNotFoundException
     */
    protected function parseSortParameter(Request $request, array $attributes, array $default): array
    {
        $orderBy = [];
        // Incoming sortCols example: ['title', '-read_time']
        $sortCols = $this->parseApiListParameters('sort', ',', $request);

        if (empty($sortCols)) {
            return [$default];
        }

        $count = count($sortCols);
        for($i = 0; $i < $count; $i++) {
            $col = starts_with($sortCols[$i], '-') ? str_after($sortCols[$i], '-') : $sortCols[$i];
            $direction = starts_with($sortCols[$i], '-') ? 'desc' : 'asc';

            if (! in_array($col, $attributes, true)) {
                throw new AttributeNotFoundException("Field '{$col}' is not available in the requested resource.");
            }
            
            $orderBy[$i]['sort_col'] = $col;
            $orderBy[$i]['sort_direction'] = $direction;
        }

        return $orderBy;
    }

    /**
     * Parses all the query parameters available in the package.
     */
    protected function parseQueryParameters(Request $request, array $relations, array $attributes): array
    {
        return [
            $this->parseWithParameter($request, $relations),
            $this->parseWhereParameter($request, $attributes)
        ];
    }

    /**
     * Parses a string and build an array with 3 items [$field, $operator, $value]
     */
    private function structureWhereFromString(string $where): array
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
