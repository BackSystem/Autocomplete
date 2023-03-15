<?php

namespace BackSystem\Autocomplete;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @template T of object
 */
final class ResultsExecutor
{
    public function __construct(private readonly ManagerRegistry $managerRegistry, private readonly ?Security $security = null)
    {
    }

    /**
     * @param ApiInterface<T> $autocompleter
     */
    public function fetchResults(ApiInterface $autocompleter, string $query): Results
    {
        if ($this->security && !$autocompleter->isGranted($this->security)) {
            throw new AccessDeniedException('Access denied from autocompleter class.');
        }

        /** @var EntityRepository<T> $repository */
        $repository = $this->managerRegistry->getRepository($autocompleter->getEntityClass());

        $queryBuilder = $autocompleter->createFilteredQueryBuilder($repository, $query);

        if (!$queryBuilder->getMaxResults()) {
            $queryBuilder->setMaxResults(5);
        }

        $results = [];

        foreach ($queryBuilder->getQuery()->getResult() as $entity) {
            $results[] = [
                'label' => $autocompleter->getLabel($entity),
                'value' => $autocompleter->getValue($entity),
            ];
        }

        return new Results($results);
    }
}
