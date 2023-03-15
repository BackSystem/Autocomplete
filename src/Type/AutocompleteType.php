<?php

namespace BackSystem\Autocomplete\Type;

use BackSystem\Autocomplete\ApiInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * @template T of object
 */
class AutocompleteType extends AbstractType
{
    /** @var array<ApiInterface<T>> */
    private array $apis = [];

    public function __construct(private readonly RouterInterface $router, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('class')->setRequired('placeholder');

        $resolver->setDefaults([
            'compound' => false,
            'multiple' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var class-string<ApiInterface<T>> $className */
        $className = $options['class'];

        $builder->addModelTransformer(new CallbackTransformer(function ($value) {
            if (null === $value) {
                return [];
            }

            if ($value instanceof Collection) {
                return $value->map(fn ($d) => (string) $d->getId())->toArray();
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            return array_map(static fn ($d) => (string) $d->getId(), $value);
        }, function ($itemsToEntities) use ($options, $className) {
            if (is_array($itemsToEntities)) {
                $itemsToEntities = array_filter($itemsToEntities, static fn ($item) => $item);
            }

            if ($options['multiple']) {
                if (!empty($itemsToEntities)) {
                    $repository = $this->entityManager->getRepository($this->getApi($className)->getEntityClass());

                    $entities = new ArrayCollection();

                    foreach ($itemsToEntities as $itemToEntity) {
                        $entity = $this->getApi($className)->isValid($repository, $itemToEntity);

                        if ($entity) {
                            $entities->add($entity);
                        }
                    }

                    return $entities;
                }

                return new ArrayCollection();
            }

            if (null !== $itemsToEntities) {
                $repository = $this->entityManager->getRepository($this->getApi($className)->getEntityClass());

                return $this->getApi($className)->isValid($repository, $itemsToEntities);
            }

            return null;
        }));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var class-string<ApiInterface<T>> $className */
        $className = $options['class'];

        $view->vars['choice_translation_domain'] = false;
        $view->vars['expanded'] = false;

        $view->vars['placeholder'] = $options['placeholder'];
        $view->vars['multiple'] = $options['multiple'];

        $view->vars['preferred_choices'] = [];

        if (true === $view->vars['multiple']) {
            $view->vars['full_name'] .= '[]';
        }

        $view->vars['choices'] = $this->choices($form->getData(), $className);

        $parsedUrl = parse_url($this->getApi($className)->getUrl());
        $path = $parsedUrl['path'] ?? null;
        $query = $parsedUrl['query'] ?? '';

        if (!$path) {
            throw new \RuntimeException('Unable to retrieve the URL.');
        }

        parse_str($query, $query);

        $queries = array_merge(['endpoint' => ltrim($path, '/')], $query);

        $view->vars['attr']['data-url'] = $this->router->generate('autocomplete_search', $queries, 0);
    }

    public function getBlockPrefix(): string
    {
        return 'choice';
    }

    /**
     * @param class-string<ApiInterface<T>> $className
     *
     * @return ApiInterface<T>
     */
    private function getApi(string $className): ApiInterface
    {
        if (!isset($this->apis[$className])) {
            $this->apis[$className] = new $className();
        }

        return $this->apis[$className];
    }

    /**
     * @param Collection<int, T>|array<T>|T|null $data
     * @param class-string<ApiInterface<T>>      $className
     *
     * @return ChoiceView[]
     */
    private function choices(mixed $data, string $className): array
    {
        if (null === $data) {
            return [];
        }

        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        if (is_array($data)) {
            return array_map(fn ($entity) => $this->getChoiceView($entity, $className), $data);
        }

        return [$this->getChoiceView($data, $className)];
    }

    /**
     * @param T                             $entity
     * @param class-string<ApiInterface<T>> $className
     */
    private function getChoiceView(mixed $entity, string $className): ChoiceView
    {
        $api = $this->getApi($className);

        return new ChoiceView($entity, $api->getValue($entity), $api->getLabel($entity));
    }
}
