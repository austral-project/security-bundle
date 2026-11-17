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

use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Repository\RoleRepository;
use Austral\EntityBundle\EntityManager\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\QueryException;

/**
 * Austral Role Manager.
 * @template T of RoleInterface
 *
 * @extends EntityManager<T>>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class RoleEntityManager extends EntityManager
{

  /**
   * @param class-string<T>|null $entity
   * @return RoleRepository<T>|null
   */
  public function getRepository(?string $entity = null): ?RoleRepository
  {
    /** @var RoleRepository<T>|null $repository */
    $repository = $this->repository;
    return $repository;
  }

  /**
   * @param array<string, mixed> $values
   *
   * @return RoleInterface<T>
   */
  public function create(array $values = array()): RoleInterface
  {
    /** @var RoleInterface<T> $object */
    $object = parent::create($values);
    return $object;
  }

  /**
   * @return Collection<int, RoleInterface<T>>
   * @throws QueryException
   */
  public function selectRoles(): Collection
  {
    return $this->getRepository()?->selectRoles() ?? new ArrayCollection();
  }

}
