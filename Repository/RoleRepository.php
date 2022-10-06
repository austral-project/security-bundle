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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\QueryException;

/**
 * Austral Role Repository.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class RoleRepository extends EntityRepository implements EntityRepositoryInterface
{

  /**
   * @return ArrayCollection|array
   * @throws QueryException
   */
  public function selectRoles()
  {
    $query = $this->createQueryBuilder('root')
      ->indexBy("root", "root.role")
      ->groupBy("root.role")
      ->addGroupBy("root.id")
      ->getQuery();
    try {
      $objects = $query->execute();
    } catch (\Doctrine\Orm\NoResultException $e) {
      $objects = array();
    }
    return $objects;
  }



}
