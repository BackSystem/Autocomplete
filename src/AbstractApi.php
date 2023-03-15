<?php

namespace BackSystem\Autocomplete;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @template T of object
 *
 * @template-implements ApiInterface<T>
 */
abstract class AbstractApi implements ApiInterface
{
    /**
     * @param EntityRepository<T> $repository
     *
     * @return T|null
     */
    public function isValid(EntityRepository $repository, mixed $id): ?object
    {
        return $repository->find($id);
    }

    public function isGranted(Security $security): bool
    {
        return true;
    }

    /**
     * @param T $entity
     */
    public function getValue(mixed $entity): string
    {
        return method_exists($entity, 'getId') ? $entity->getId() : 'Unknown';
    }

    /**
     * @param T $entity
     */
    public function getLabel(mixed $entity): string
    {
        return method_exists($entity, 'getId') ? $entity->getId() : 'Unknown';
    }
}
