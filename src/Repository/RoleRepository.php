<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Repository;

use Austral\EntityBundle\Repository\EntityRepository;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;

/**
 * Austral Role Repository.
 *
 * @extends EntityRepository<RoleInterface>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class RoleRepository extends EntityRepository
{
  /**
   * All the roles, by role ("ROLE_...").
   *
   * @return array<string, RoleInterface>
   */
  public function selectRoles(): array
  {
    $roles = [];
    foreach ($this->createQueryBuilder('root')->orderBy('root.role', 'ASC')->getQuery()->getResult() as $role)
    {
      if ($role instanceof RoleInterface)
      {
        $roles[$role->getRole()] = $role;
      }
    }

    return $roles;
  }
}
