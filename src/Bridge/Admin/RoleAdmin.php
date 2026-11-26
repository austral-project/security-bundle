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
use Austral\FilterBundle\Filter\SearchFilter;
use Austral\FormBundle\Field\TextField;
use Austral\ListBundle\Column\ValueColumn;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Austral Role Admin.
 *
 * Module security/roles: the roles saved in the database (those of the modules are created by the groups and
 * the command austral:admin:roles). A user writes only a role he has: he cannot turn a role of his groups into
 * another one.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class RoleAdmin extends Admin
{
  /**
   * @param AuthorizationCheckerInterface $authorizationChecker
   */
  public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
  {
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
      $event->getQueryBuilder()->orderBy($event->getRootAlias().'.role', 'ASC');
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
      ->add(new ValueColumn('role'));
  }

  /**
   * @param FilterAdminEvent $event
   *
   * @return void
   */
  public function configureFilterMapper(FilterAdminEvent $event): void
  {
    $event->getMapper()->filter()->add(new SearchFilter('search', ['properties' => ['name', 'role']]));
  }

  /**
   * @param FormAdminEvent $event
   *
   * @return void
   */
  public function configureFormMapper(FormAdminEvent $event): void
  {
    $role = $event->getObject();
    $event->getMapper()->addFieldset('generalInformation', 'fieldset.generalInformation')
      ->add(TextField::create('name', ['required' => true, 'constraints' => [new NotBlank()], 'form_options' => ['empty_data' => '']]))
      ->add(TextField::create('role', [
        'required' => true,
        'form_options' => ['empty_data' => ''],
        'visible' => $event->isCreate() || ($role instanceof RoleInterface && $this->authorizationChecker->isGranted($role->getRole())),
        'constraints' => [new NotBlank(), new Regex(pattern: '/^ROLE_[A-Z0-9_]+$/', message: 'errors.role.format')],
      ]));
  }

  /**
   * @param FormAdminEvent $event
   *
   * @return void
   */
  protected function formSaveBefore(FormAdminEvent $event): void
  {
    $role = $event->getObject();
    if ($role instanceof RoleInterface && !$this->authorizationChecker->isGranted($role->getRole()))
    {
      throw new AccessDeniedException(\sprintf('You cannot give the role "%s": you do not have it.', $role->getRole()));
    }
  }
}
