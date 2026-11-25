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
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Repository\GroupRepository;

/**
 * Austral Group Entity Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends EntityManager<object>
 */
class GroupEntityManager extends EntityManager
{
  /**
   * @param string|null $entity
   *
   * @return GroupRepository|null
   */
  public function getRepository(?string $entity = null): ?GroupRepository
  {
    return $this->repository instanceof GroupRepository ? $this->repository : null;
  }

  /**
   * @param array<string, mixed> $values
   *
   * @return GroupInterface
   */
  public function create(array $values = []): GroupInterface
  {
    $group = parent::create($values);
    if (!$group instanceof GroupInterface)
    {
      throw new \LogicException(\sprintf('The group class must implement "%s".', GroupInterface::class));
    }

    return $group;
  }

  /**
   * @param string        $keyname
   * @param callable|null $closure Called with the query builder
   *
   * @return GroupInterface|null
   */
  public function retrieveByKeyname(string $keyname, ?callable $closure = null): ?GroupInterface
  {
    return $this->getRepository()?->retrieveByKeyname($keyname, $closure);
  }
}
