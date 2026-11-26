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

use Austral\AdminBundle\Event\FormAdminEvent;
use Austral\AdminBundle\Event\ListAdminEvent;
use Austral\FormBundle\Field\SelectField;
use Austral\ListBundle\Column\ChoicesColumn;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Austral Admin User Admin.
 *
 * Module security/admin-users: the administrators (types admin and root). A user who is not root does not see the
 * root users.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class AdminUserAdmin extends AbstractUserAdmin
{
  /**
   * {@inheritdoc}
   */
  public function configureListMapper(ListAdminEvent $event): void
  {
    parent::configureListMapper($event);
    $event->getMapper()->add(new ChoicesColumn('typeUser', [
      'choices' => $this->types(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  protected function configureTypeFields(FormAdminEvent $event): void
  {
    $choices = [];
    foreach ($this->types() as $type => $label)
    {
      $choices[$label] = $type;
    }
    $event->getMapper()->addFieldset('generalInformation')
      ->add(SelectField::create('typeUser', $choices, ['required' => true]));
  }

  /**
   * {@inheritdoc}
   */
  protected function restrict(QueryBuilder $queryBuilder, string $alias): void
  {
    $queryBuilder->andWhere($queryBuilder->expr()->in("{$alias}.typeUser", ':austral_admin_user_types'))
      ->setParameter('austral_admin_user_types', array_keys($this->types()));
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultType(): string
  {
    return UserInterface::USER_TYPE_ADMIN;
  }

  /**
   * {@inheritdoc}
   */
  protected function isTypeAllowed(string $type): bool
  {
    return \array_key_exists($type, $this->types());
  }

  /**
   * @return array<string, string> Types the current user sees and gives => label
   */
  private function types(): array
  {
    $types = [UserInterface::USER_TYPE_ADMIN => 'choices.user.type.admin'];
    if ($this->isRoot())
    {
      $types[UserInterface::USER_TYPE_ROOT] = 'choices.user.type.root';
    }

    return $types;
  }
}
