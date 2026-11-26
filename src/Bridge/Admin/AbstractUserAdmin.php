<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Bridge\Admin;

use Austral\AdminBundle\Admin\Admin;
use Austral\AdminBundle\Event\AdminEvents;
use Austral\AdminBundle\Event\FilterAdminEvent;
use Austral\AdminBundle\Event\FormAdminEvent;
use Austral\AdminBundle\Event\ListAdminEvent;
use Austral\AdminBundle\Event\ObjectAdminEvent;
use Austral\AdminBundle\Event\QueryAdminEvent;
use Austral\AdminBundle\Page\EntityQuery;
use Austral\FilterBundle\Filter\BooleanFilter;
use Austral\FilterBundle\Filter\SearchFilter;
use Austral\FormBundle\Field\EntityField;
use Austral\FormBundle\Field\PasswordField;
use Austral\FormBundle\Field\SelectField;
use Austral\FormBundle\Field\SwitchField;
use Austral\FormBundle\Field\TextField;
use Austral\ListBundle\Column\SwitchColumn;
use Austral\ListBundle\Column\ValueColumn;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Austral Abstract User Admin.
 *
 * Common part of the users and the administrators. A user who is not root does not see, change or give the type
 * root, and gives only the groups whose roles he has; nobody deletes or disables his own account here.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
abstract class AbstractUserAdmin extends Admin
{
  /**
   * @param TokenStorageInterface         $tokenStorage
   * @param AuthorizationCheckerInterface $authorizationChecker
   * @param class-string<GroupInterface>  $groupClass
   * @param list<string>                  $languages            Languages of the interface
   */
  public function __construct(
    protected readonly TokenStorageInterface $tokenStorage,
    protected readonly AuthorizationCheckerInterface $authorizationChecker,
    protected readonly string $groupClass,
    protected readonly array $languages = ['en', 'fr'],
  ) {
  }

  /**
   * @return array<string, string>
   */
  public function getEvents(): array
  {
    return [
      AdminEvents::FORM_SAVE_BEFORE => 'formSaveBefore',
      AdminEvents::DELETE_BEFORE => 'deleteBefore',
    ];
  }

  /**
   * @param QueryBuilder $queryBuilder
   * @param string       $alias
   *
   * @return void
   */
  abstract protected function restrict(QueryBuilder $queryBuilder, string $alias): void;

  /**
   * @return string Type given to a new user
   */
  abstract protected function defaultType(): string;

  /**
   * @param QueryAdminEvent $event
   *
   * @return void
   */
  public function configureQuery(QueryAdminEvent $event): void
  {
    $this->restrict($event->getQueryBuilder(), $event->getRootAlias());
    if ($event->is(QueryAdminEvent::CONTEXT_LIST) || $event->is(QueryAdminEvent::CONTEXT_EXPORT))
    {
      $event->getQueryBuilder()->orderBy($event->getRootAlias().'.username', 'ASC');
    }
  }

  /**
   * @param ListAdminEvent $event
   *
   * @return void
   */
  public function configureListMapper(ListAdminEvent $event): void
  {
    $current = $this->currentUser();
    $toggle = $event->toggle('isActive');
    $event->getMapper()
      ->add(new ValueColumn('username'))
      ->add(new ValueColumn('email'))
      ->add(new ValueColumn('firstname'))
      ->add(new ValueColumn('lastname'))
      ->add(new SwitchColumn('isActive', [
        'label' => 'fields.enabled.entitled',
        'url' => $toggle['url'],
        'granted' => static fn (object|array|null $user = null): bool => $user instanceof UserInterface && $user->getId() !== $current?->getId() && $toggle['granted']($user),
      ]));
  }

  /**
   * @param FilterAdminEvent $event
   *
   * @return void
   */
  public function configureFilterMapper(FilterAdminEvent $event): void
  {
    $event->getMapper()->filter()
      ->add(new SearchFilter('search', ['properties' => ['username', 'email', 'firstname', 'lastname']]))
      ->add(new BooleanFilter('isActive', ['label' => 'fields.enabled.entitled']));
  }

  /**
   * @param FormAdminEvent $event
   *
   * @return void
   */
  public function configureFormMapper(FormAdminEvent $event): void
  {
    $user = $event->getObject();
    if (!$user instanceof UserInterface)
    {
      return;
    }
    $self = $user->getId() === $this->currentUser()?->getId();
    $languages = [];
    foreach ($this->languages as $language)
    {
      $languages["choices.language.{$language}"] = $language;
    }

    $mapper = $event->getMapper();
    $mapper->addFieldset('generalInformation', 'fieldset.generalInformation')
      ->add(TextField::create('username', self::requiredText()))
      ->add(TextField::create('email', self::requiredText()))
      ->add(TextField::create('firstname'))
      ->add(TextField::create('lastname'))
      ->add(SelectField::create('language', $languages));
    $this->configureTypeFields($event);

    $rights = $mapper->addFieldset('rights', 'fieldset.permissions', 'right');
    $rights->add(SwitchField::create('isActive', ['label' => 'fields.enabled.entitled', 'visible' => !$self]));
    $rights->add(EntityField::create('groups', $this->groupClass, [
      'form_options' => [
        'multiple' => true,
        'expanded' => true,
        'by_reference' => false,
        'query_builder' => fn (EntityRepository $repository): QueryBuilder => $repository->createQueryBuilder('g')->orderBy('g.name', 'ASC'),
        'choice_filter' => fn (?GroupInterface $group): bool => null === $group || $this->canGiveGroup($group),
      ],
    ]));

    $constraints = [new Regex(pattern: PasswordField::MATCH_DEFAULT, message: 'errors.plainPassword.regex')];
    if ($event->isCreate())
    {
      $constraints[] = new NotBlank(message: 'errors.plainPassword.blank');
    }
    $mapper->addFieldset('password', 'fieldset.connexionInformation')
      ->add(PasswordField::create('plainPassword', [
        'auto_constraints' => false,
        'required' => $event->isCreate(),
        'constraints' => $constraints,
      ]));
  }

  /**
   * Fields of the type of the user (none for the users).
   *
   * @param FormAdminEvent $event
   *
   * @return void
   */
  protected function configureTypeFields(FormAdminEvent $event): void
  {
  }

  /**
   * @param FormAdminEvent $event
   *
   * @return void
   */
  protected function formSaveBefore(FormAdminEvent $event): void
  {
    $user = $event->getObject();
    if (!$user instanceof UserInterface)
    {
      return;
    }
    if ($event->isCreate() || !$this->isTypeAllowed($user->getTypeUser()))
    {
      $user->setTypeUser($this->isTypeAllowed($user->getTypeUser()) ? $user->getTypeUser() : $this->defaultType());
    }
    if (UserInterface::USER_TYPE_ROOT === $user->getTypeUser() && !$this->isRoot())
    {
      throw new AccessDeniedException('Only a root user gives the type root.');
    }
    // The groups the user cannot give are not in the form: the ones the account had are given back.
    $groups = $user->getGroups();
    $originals = $groups instanceof PersistentCollection ? array_filter($groups->getSnapshot(), static fn (mixed $group): bool => $group instanceof GroupInterface) : [];
    foreach ($originals as $original)
    {
      if (!$this->canGiveGroup($original) && !$groups->contains($original))
      {
        $user->addGroup($original);
      }
    }
    foreach ($groups->toArray() as $group)
    {
      if (!$this->canGiveGroup($group) && !\in_array($group, $originals, true))
      {
        throw new AccessDeniedException(\sprintf('The group "%s" has roles you do not have.', $group->getKeyname()));
      }
    }
  }

  /**
   * @param ObjectAdminEvent $event
   *
   * @return void
   */
  protected function deleteBefore(ObjectAdminEvent $event): void
  {
    $user = $event->getObject();
    if ($user instanceof UserInterface && $user->getId() === $this->currentUser()?->getId())
    {
      throw new AccessDeniedException('You cannot delete your own account.');
    }
  }

  /**
   * @param string $type
   *
   * @return bool
   */
  protected function isTypeAllowed(string $type): bool
  {
    return $type === $this->defaultType();
  }

  /**
   * @return UserInterface|null
   */
  protected function currentUser(): ?UserInterface
  {
    $user = $this->tokenStorage->getToken()?->getUser();

    return $user instanceof UserInterface ? $user : null;
  }

  /**
   * @return bool
   */
  protected function isRoot(): bool
  {
    return true === $this->currentUser()?->isSuperAdmin();
  }

  /**
   * A group is given by a user who has all its roles.
   *
   * @param GroupInterface $group
   *
   * @return bool
   */
  protected function canGiveGroup(GroupInterface $group): bool
  {
    if ($this->isRoot())
    {
      return true;
    }
    foreach ($group->getRolesArray() as $role)
    {
      if (!$this->authorizationChecker->isGranted($role))
      {
        return false;
      }
    }

    return true;
  }

  /**
   * @return string
   */
  protected static function alias(): string
  {
    return EntityQuery::ROOT_ALIAS;
  }

  /**
   * Options of a required text: never null (empty_data) and not blank.
   *
   * @return array<string, mixed>
   */
  protected static function requiredText(): array
  {
    return ['required' => true, 'constraints' => [new NotBlank()], 'form_options' => ['empty_data' => '']];
  }
}
