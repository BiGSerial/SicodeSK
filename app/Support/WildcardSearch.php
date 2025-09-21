<?php

namespace App\Support;

class WildcardSearch
{
    public function __construct(
        public readonly string $type,
        public readonly string $search,
    ) {
    }

    public function isLike(): bool
    {
        return $this->type === 'like';
    }

    public function isEqual(): bool
    {
        return $this->type === '=';
    }
}
