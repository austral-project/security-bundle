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

use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Repository\GroupRepository;

use Austral\EntityBundle\EntityManager\EntityManager;

/**
 * Austral Group Manager.
 * @template T of object
 *
 * @extends EntityManager<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class GroupEntityManager extends EntityManager
{

  /**
   * @param class-string<T>|null $entity
   * @return GroupRepository<T>|null
   */
  public function getRepository(?string $entity = null): ?GroupRepository
  {
    /** @var GroupRepository<T>|null $repository */
    $repository = $this->repository;
    return $repository;
  }

  /**
   * @param array<string, mixed> $values
   *
   * @return GroupInterface<T>
   */
  public function create(array $values = array()): GroupInterface
  {
    /** @var GroupInterface<T> $object */
    $object = parent::create($values);
    return $object;
  }

  /**
   * @param string $keyname
   * @param \Closure(object): mixed|null $closure
   *
   * @return GroupInterface<T>|null
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function retrieveByKeyname(string $keyname, ?\Closure $closure = null): ?GroupInterface
  {
    return $this->getRepository()?->retrieveByKeyname($keyname, $closure) ?? null;
  }

}
