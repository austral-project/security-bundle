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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\QueryException;

/**
 * Austral Role Repository.
 * @template T of object
 *
 * @extends EntityRepository<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class RoleRepository extends EntityRepository
{

  /**
   * @return Collection<int, RoleInterface<T>>
   * @throws QueryException
   */
  public function selectRoles(): Collection
  {
    $query = $this->createQueryBuilder('root')
      ->indexBy("root", "root.role")
      ->groupBy("root.role")
      ->addGroupBy("root.id")
      ->getQuery();
    try {
      $objects = $query->execute();
    } catch (\Exception $e) {
      $objects = array();
    }
    return $objects;
  }



}
