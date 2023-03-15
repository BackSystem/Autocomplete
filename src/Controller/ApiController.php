<?php

namespace BackSystem\Autocomplete\Controller;

use BackSystem\Autocomplete\Registry;
use BackSystem\Autocomplete\ResultsExecutor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template T of object
 */
class ApiController
{
    /**
     * @param Registry<T>        $autocompleterRegistry
     * @param ResultsExecutor<T> $autocompleteResultsExecutor
     */
    public function __construct(private readonly Registry $autocompleterRegistry, private readonly ResultsExecutor $autocompleteResultsExecutor)
    {
    }

    public function __invoke(string $endpoint, Request $request): JsonResponse
    {
        $queries = $request->query->all();

        unset($queries['query']);

        if (!empty($queries)) {
            $endpoint .= '?'.http_build_query($queries);
        }

        $autocompleter = $this->autocompleterRegistry->getAutocompleter('/'.$endpoint);

        if (!$autocompleter) {
            throw new NotFoundHttpException(sprintf('Not found for "%s". Available: (%s)', $endpoint, implode(', ', $this->autocompleterRegistry->getAutocompleterNames())));
        }

        $query = (string) $request->query->get('query', '');

        $data = $this->autocompleteResultsExecutor->fetchResults($autocompleter, $query);

        return new JsonResponse([
            'results' => $data->getResults(),
        ]);
    }
}
