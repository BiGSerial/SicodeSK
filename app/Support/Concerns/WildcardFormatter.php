<?php

namespace App\Support\Concerns;

trait WildcardFormatter
{
    protected function formatWildcard(?string $value, $mySql = false): ?object
    {
        $term = trim((string) $value);

        if ($term === '') {
            return null;
        }

        $term = str_replace(['?', '*'], '%', $term);

        if (str_contains($term, '%') === false) {
            $type = $mySql ? "=" : "ILIKE";

        } else {
            $type = $mySql ? "LIKE" : "ILIKE";
        }

        return (object) [
            'term' => $term,
            'type' => $type,
        ];
    }
}
