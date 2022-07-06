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

use Austral\EntityBundle\Entity\EntityInterface;
use Austral\EntityBundle\EntityManager\EntityManager;
use Austral\EntityBundle\EntityManager\EntityManagerInterface;

/**
 * Austral Group Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class GroupEntityManager extends EntityManager implements EntityManagerInterface
{

  /**
   * @var GroupRepository
   */
  protected $repository;

  /**
   * @param array $values
   *
   * @return GroupInterface|EntityInterface
   */
  public function create(array $values = array()): GroupInterface
  {
    $class = $this->getClass();
    /** @var GroupInterface|EntityInterface $object */
    $object = new $class;
    $object->hydrate($values);
    return $object;
  }

  /**
   * @param string $keyname
   * @param \Closure|null $closure
   *
   * @return mixed
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function retreiveByKeyname(string $keyname, \Closure $closure = null): GroupInterface
  {
    return $this->repository->retreiveByKeyname($keyname, $closure);
  }


}
