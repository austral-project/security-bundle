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
use Austral\EntityBundle\Repository\RepositoryInterface;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Austral Group Repository.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class GroupRepository extends EntityRepository implements RepositoryInterface
{

  /**
   * @param string $keyname
   * @param \Closure|null $closure
   *
   * @return GroupInterface|null
   * @throws NonUniqueResultException
   */
  public function retreiveByKeyname(string $keyname, \Closure $closure = null): ?GroupInterface
  {
    return $this->retreiveByKey("keyname", $keyname, $closure);
  }

}
