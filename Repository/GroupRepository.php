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
use Austral\EntityBundle\Repository\EntityRepositoryInterface;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Austral Group Repository.
 * @template T of object
 *
 * @extends EntityRepository<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class GroupRepository extends EntityRepository
{

  /**
   * @param string $keyname
   * @param \Closure(object): mixed|null $closure
   *
   * @return GroupInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function retrieveByKeyname(string $keyname, ?\Closure $closure = null): ?GroupInterface
  {
    /** @var GroupInterface<T> $object */
    $object =  $this->retrieveByKey("keyname", $keyname, $closure);
    return $object;
  }

}
