<?php

namespace BackSystem\Autocomplete;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @template T of object
 */
interface ApiInterface
{
    /**
     * @return class-string<T>
     */
    public function getEntityClass(): string;

    public function getUrl(): string;

    /**
     * @param EntityRepository<T> $repository
     */
    public function createFilteredQueryBuilder(EntityRepository $repository, string $query): QueryBuilder;

    /**
     * @param EntityRepository<T> $repository
     *
     * @return T|null
     */
    public function isValid(EntityRepository $repository, mixed $id): ?object;

    public function isGranted(Security $security): bool;

    /**
     * @param T $entity
     */
    public function getValue(mixed $entity): string;

    /**
     * @param T $entity
     */
    public function getLabel(mixed $entity): string;
}
