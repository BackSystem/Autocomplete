<?php

namespace BackSystem\Autocomplete;

final class Results
{
    /**
     * @param list<array{label: string, value: mixed}> $results
     */
    public function __construct(private readonly array $results)
    {
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
