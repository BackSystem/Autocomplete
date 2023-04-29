# Autocomplete

## Information

This Symfony bundle has been created on the basis of [Symfony UX Autocomplete](https://github.com/symfony/ux-autocomplete). The latter meets other needs and will not be suitable for as many people as [Symfony UX Autocomplete](https://github.com/symfony/ux-autocomplete).

## Installation

First, install the bundle with the following command:
```shell
composer require backsystem/autocomplete
```

Next, configure the bundle by creating the following file in `config/routes/autocomplete.yaml`:

```yaml
autocomplete:
    resource: '@AutocompleteBundle/config/routes.yaml'
    prefix: /api/search # You can choose the prefix you want

```

## Example

```php
<?php

namespace App\Api;

use App\Entity\Member;
use App\Repository\MemberRepository;
use BackSystem\Autocomplete\AbstractApi;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @template-extends AbstractApi<User>
 */
class UserApi extends AbstractApi {

    public function getEntityClass(): string {
        return User::class;
    }

    public function getUrl(): string {
        return '/users';
    }

    public function createFilteredQueryBuilder(EntityRepository $repository, string $query): QueryBuilder {
        return $repository->createQueryBuilder('user')
            ->andWhere('CONCAT(user.id, user.firstName, user.lastName, user.firstName, user.email) LIKE :search')
            ->setParameter('search', '%' . str_replace(' ', '', $query) . '%');
    }

    public function isValid(EntityRepository $repository, mixed $id): ?object {
        return $repository->createQueryBuilder('user')
            ->andWhere('user.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getValue($entity): string {
        return (string) $entity->getId();
    }

    public function getLabel($entity): string {
        return sprintf('<b>%s</b><span> - </span><i>%s</i><br><small>%s</small>', str_pad($entity->getId(), 5, '0', STR_PAD_LEFT), $entity->getFullName(), $entity->getEmail());
    }

    public function getTitle($entity): string {
        return $entity->getFullName(); // Optional method, try with and without! :)
    }

}

```

```php
<?php

namespace App\Form;

use App\Api\UserApi;
use App\Entity\Post;
use BackSystem\Autocomplete\Type\AutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder->add('title', TextType::class, [
            'label' => 'Amazing title',
        ])->add('user', AutocompleteType::class, [
            'class' => UserApi::class,
            'placeholder' => 'Choose an user',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }

}

```