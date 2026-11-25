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
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;

/**
 * Austral Group Repository.
 *
 * @extends EntityRepository<GroupInterface>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class GroupRepository extends EntityRepository
{
  /**
   * @param string        $keyname
   * @param callable|null $closure Called with the query builder
   *
   * @return GroupInterface|null
   */
  public function retrieveByKeyname(string $keyname, ?callable $closure = null): ?GroupInterface
  {
    $group = $this->retrieveByKey('keyname', $keyname, $closure);

    return $group instanceof GroupInterface ? $group : null;
  }
}
