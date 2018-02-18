<?php

namespace ReaDev\ApiHelpers\Traits;

use Illuminate\Database\Eloquent\Builder;
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
     * Parses the "include" query string parameter from the request.
     *
     * @throws RelationNotFoundException
     */
    protected function parseIncludeParameter(Request $request, array $relations): array
    {
        $includes = $this->parseApiListParameters('include', ',', $request);

        foreach ($includes as $include) {
            if (! in_array($include, $relations, true)) {
                throw new RelationNotFoundException("Relation '{$include}' does not exists in the resource.");
            }
        }

        return $includes;
    }

    protected function parseQueryParameter(Request $request, array $relations)
    {
        [$field, $search] = $this->parseApiListParameters('q', ':', $request);
        // TODO: Validate that search does not has more than 100 characters, throw an exeption if so.
        if (str_contains($field, '.')) {
            [$relation, $field] = explode('.', $field);
            // TODO: Check if relation exists on the model, if not, throw an exception
            // TODO: Check if field exists on the related model, if not, throw an exception
            return [
                'where_has' => [
                    [
                        'relation' => $relation,
                        'field' => $field,
                        'operator' => 'LIKE',
                        'value' => "%{$search}%"
                    ]
                ]
            ];
        }
        // TODO: Check if field exists on the model, if not, throw an exception
        return [
            'where' => [
                [
                    'field' => $field,
                    'operator' => 'LIKE',
                    'value' => "%{$search}%"
                ]
            ]
        ];
    }

    /**
     * Parses the "filter" query string and returns a criteria array.
     *
     * @param Request $request
     * @param array   $available Model attributes and relations that are searchable
     *
     * @return array
     * @throws AttributeNotFoundException|RelationNotFoundException
     */
    protected function parseFilterParameter(Request $request, array $available): array
    {

        $criteria = [];
        $filters = $this->parseApiListParameters('filter', ',', $request);

        foreach ($filters as $filter) {
            [$field, $operator, $value] = $this->structureWhereFromString($filter);
            
            if (str_contains($field, '.')) {
                // If relations key is null then the resource does not have relations
                if ($available['relations'] === null) {
                    continue;
                }
                
                [$relation, $field] = explode('.', $field);
                // Verify the requested relation field exists
                if (! array_key_exists($relation, $available['relations'])) {
                    throw new RelationNotFoundException("Relation '{$relation}' does not exists in the resource.");
                }
                if (! in_array($field, $available['relations'][$relation], true)) {
                    throw new AttributeNotFoundException(
                        "Field '{$field}' is not available in the requested resource '{$relation}'."
                    );
                }

                $criteria['where_has'][] = [
                    'relation' => $relation,
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $this->castIfBoolOrNull($value)
                ];
                continue;
            }
            // We need to verify that the requested field exists in the model
            if (! in_array($field, $available['self'], true)) {
                throw new AttributeNotFoundException("Field '{$field}' is not available in the requested resource.");
            }

            $criteria['where'][] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $this->castIfBoolOrNull($value)
            ];
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
        for ($i = 0; $i < $count; $i++) {
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

    protected function parsePerPageParameter(Request $request, int $default = 10): int
    {
        $limit = (int) $request->query('per_page', $default);

        if (is_int($limit) && $limit > 0) {
            return $limit;
        }
        return $default;
    }

    protected function parsePaginateParameter(Request $request, bool $default = false): bool
    {
        return (bool) $request->query('paginate', $default);
    }

    /**
     * Parses all the query parameters available in the package.
     */
    protected function parseQueryParameters(Request $request, array $relations, array $attributes): array
    {
        return [
            $this->parseIncludeParameter($request, $relations),
            $this->parseFilterParameter($request, $attributes)
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

    protected function passFiltersToQueryBuilder(array $filters, Builder $query): Builder
    {
        foreach ($filters as $type => $filter) {
            foreach ($filter as $item) {
                if ($type === 'where_has') {
                    $query->whereHas($item['relation'], function (Builder $query) use ($item) {
                        $query->where($item['field'], $item['operator'], $item['value']);
                    });
                }
                if ($type === 'where') {
                    $query->where($item['field'], $item['operator'], $item['value']);
                }
            }
        }

        return $query;
    }
}
