<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\EntityManager;

use Austral\EntityBundle\EntityManager\EntityManager;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Repository\RoleRepository;

/**
 * Austral Role Entity Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends EntityManager<object>
 */
class RoleEntityManager extends EntityManager
{
  /**
   * @param string|null $entity
   *
   * @return RoleRepository|null
   */
  public function getRepository(?string $entity = null): ?RoleRepository
  {
    return $this->repository instanceof RoleRepository ? $this->repository : null;
  }

  /**
   * @param array<string, mixed> $values
   *
   * @return RoleInterface
   */
  public function create(array $values = []): RoleInterface
  {
    $role = parent::create($values);
    if (!$role instanceof RoleInterface)
    {
      throw new \LogicException(\sprintf('The role class must implement "%s".', RoleInterface::class));
    }

    return $role;
  }

  /**
   * All the roles, by role ("ROLE_...").
   *
   * @return array<string, RoleInterface>
   */
  public function selectRoles(): array
  {
    return $this->getRepository()?->selectRoles() ?? [];
  }
}
