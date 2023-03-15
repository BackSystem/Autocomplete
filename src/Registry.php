<?php

namespace BackSystem\Autocomplete;

use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @template T of object
 */
final class Registry
{
    public function __construct(private readonly ServiceLocator $autocompletersLocator)
    {
    }

    /**
     * @return ApiInterface<T>|null
     */
    public function getAutocompleter(string $alias): ?ApiInterface
    {
        return $this->autocompletersLocator->has($alias) ? $this->autocompletersLocator->get($alias) : null;
    }

    /**
     * @return array<string>
     */
    public function getAutocompleterNames(): array
    {
        return array_keys($this->autocompletersLocator->getProvidedServices());
    }
}
