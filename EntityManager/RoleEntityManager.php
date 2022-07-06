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
use Austral\EntityBundle\Entity\EntityInterface;
use Austral\EntityBundle\EntityManager\EntityManager;
use Austral\EntityBundle\EntityManager\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\QueryException;

/**
 * Austral Role Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class RoleEntityManager extends EntityManager implements EntityManagerInterface
{

  /**
   * @var RoleRepository
   */
  protected $repository;

  /**
   * @param array $values
   *
   * @return RoleInterface|EntityInterface
   */
  public function create(array $values = array()): RoleInterface
  {
    /** @var RoleInterface|EntityInterface $object */
    $object = parent::create($values);
    $object->hydrate($values);
    return $object;
  }

  /**
   * @return array|ArrayCollection
   * @throws QueryException
   */
  public function selectRoles()
  {
    return $this->repository->selectRoles();
  }

}
