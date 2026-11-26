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
use Austral\AdminBundle\Event\QueryAdminEvent;
use Austral\AdminBundle\Security\RoleTree;
use Austral\FilterBundle\Filter\SearchFilter;
use Austral\FormBundle\Field\SwitchField;
use Austral\FormBundle\Field\TextField;
use Austral\ListBundle\Column\ValueColumn;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\EntityManager\RoleEntityManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Austral Group Admin.
 *
 * Module security/groups: a group gives roles to its users. The roles of the modules are switches, by module, in
 * the order of the navigation; a role missing in the database is created when it is given. A user gives only the
 * roles he has (a root user has them all).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class GroupAdmin extends Admin
{
  /**
   * @var array<string, RoleInterface>|null
   */
  private ?array $roles = null;

  /**
   * @param RoleTree                      $roleTree
   * @param RoleEntityManager             $roleEntityManager
   * @param AuthorizationCheckerInterface $authorizationChecker
   */
  public function __construct(
    private readonly RoleTree $roleTree,
    private readonly RoleEntityManager $roleEntityManager,
    private readonly AuthorizationCheckerInterface $authorizationChecker,
  ) {
  }

  /**
   * @return array<string, string>
   */
  public function getEvents(): array
  {
    return [AdminEvents::FORM_SAVE_BEFORE => 'formSaveBefore'];
  }

  /**
   * @param QueryAdminEvent $event
   *
   * @return void
   */
  public function configureQuery(QueryAdminEvent $event): void
  {
    if ($event->is(QueryAdminEvent::CONTEXT_LIST) || $event->is(QueryAdminEvent::CONTEXT_EXPORT))
    {
      $event->getQueryBuilder()->orderBy($event->getRootAlias().'.name', 'ASC');
    }
  }

  /**
   * @param ListAdminEvent $event
   *
   * @return void
   */
  public function configureListMapper(ListAdminEvent $event): void
  {
    $event->getMapper()
      ->add(new ValueColumn('name'))
      ->add(new ValueColumn('keyname'))
      ->add(new ValueColumn('roles', ['getter' => static fn (GroupInterface $group): string => (string) \count($group->getRolesArray())]));
  }

  /**
   * @param FilterAdminEvent $event
   *
   * @return void
   */
  public function configureFilterMapper(FilterAdminEvent $event): void
  {
    $event->getMapper()->filter()->add(new SearchFilter('search', ['properties' => ['name', 'keyname']]));
  }

  /**
   * @param FormAdminEvent $event
   *
   * @return void
   */
  public function configureFormMapper(FormAdminEvent $event): void
  {
    $mapper = $event->getMapper();
    $mapper->addFieldset('generalInformation', 'fieldset.generalInformation')
      ->add(TextField::create('name', ['required' => true, 'constraints' => [new NotBlank()], 'form_options' => ['empty_data' => '']]))
      ->add(TextField::create('keyname', ['auto_constraints' => false, 'helper' => 'helpers.keyname', 'form_options' => ['empty_data' => '']]));

    $fieldset = $mapper->addFieldset('roles', 'fieldset.permissions');
    foreach ($this->roleTree->modules() as $module)
    {
      $definition = $module['definition'];
      $roles = array_filter($module['roles'], fn (string $action, string $role): bool => $this->authorizationChecker->isGranted($role), \ARRAY_FILTER_USE_BOTH);
      if (!$roles)
      {
        continue;
      }
      $group = $fieldset->addGroup(str_replace('/', '-', $definition->getPath()), "pages.names.{$definition->getTranslationKey()}.plural");
      foreach ($roles as $role => $action)
      {
        $name = RoleTree::name($definition, $action);
        $group->add(SwitchField::create($role, [
          'label' => "roles.{$action}",
          'mapped' => true,
          'getter' => static fn (GroupInterface $group): bool => \in_array($role, $group->getRolesArray(), true),
          'setter' => function (GroupInterface &$group, mixed $value) use ($role, $name): void {
            $this->setRole($group, $role, $name, (bool) $value);
          },
        ]));
      }
    }
  }

  /**
   * @param FormAdminEvent $event
   *
   * @return void
   */
  protected function formSaveBefore(FormAdminEvent $event): void
  {
    $group = $event->getObject();
    if ($group instanceof GroupInterface && '' === trim($group->getKeyname()))
    {
      $group->setKeyname(new AsciiSlugger()->slug($group->getName(), '_')->lower()->toString());
    }
  }

  /**
   * @param GroupInterface $group
   * @param string         $role
   * @param string         $name    Name of a role created
   * @param bool           $enabled
   *
   * @return void
   */
  private function setRole(GroupInterface $group, string $role, string $name, bool $enabled): void
  {
    if (!$this->authorizationChecker->isGranted($role))
    {
      return;
    }
    $this->roles ??= $this->roleEntityManager->selectRoles();
    $entity = $this->roles[$role] ?? null;
    if (!$enabled)
    {
      if (null !== $entity)
      {
        $group->removeRole($entity);
      }

      return;
    }
    if (null === $entity)
    {
      $entity = $this->roleEntityManager->create();
      $entity->setRole($role)->setName($name);
      $this->roleEntityManager->update($entity, false);
      $this->roles[$role] = $entity;
    }
    if (!\in_array($role, $group->getRolesArray(), true))
    {
      $group->addRole($entity);
    }
  }
}
