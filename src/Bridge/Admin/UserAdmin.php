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

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Austral User Admin.
 *
 * Module security/users: the users of type "user" (accounts of the application, not of the admin).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserAdmin extends AbstractUserAdmin
{
  /**
   * {@inheritdoc}
   */
  protected function restrict(QueryBuilder $queryBuilder, string $alias): void
  {
    $queryBuilder->andWhere("{$alias}.typeUser = :austral_admin_user_type")
      ->setParameter('austral_admin_user_type', UserInterface::USER_TYPE_USER);
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultType(): string
  {
    return UserInterface::USER_TYPE_USER;
  }
}
