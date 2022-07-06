<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Repository\Base;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;

use Austral\EntityBundle\Repository\EntityRepository;
use Austral\EntityBundle\Repository\RepositoryInterface;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Austral Base User Repository.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
class BaseUserRepository extends EntityRepository implements RepositoryInterface
{

  /**
   * @param string $username
   * @param \Closure|null $closure
   *
   * @return UserInterface|null
   * @throws NonUniqueResultException
   */
  public function retreiveByUsername(string $username, \Closure $closure = null): ?UserInterface
  {
    return $this->retreiveByKey("username", $username, $closure);
  }

  /**
   * @param string $email
   * @param \Closure|null $closure
   *
   * @return UserInterface|null
   * @throws NonUniqueResultException
   */
  public function retreiveByEmail(string $email, \Closure $closure = null): ?UserInterface
  {
    return $this->retreiveByKey("email", $email, $closure);
  }

  /**
   * @param $name
   * @param QueryBuilder $queryBuilder
   *
   * @return QueryBuilder
   */
  public function queryBuilderExtends($name, QueryBuilder $queryBuilder): QueryBuilder
  {
    if($name == "retreive-by-key")
    {
      $queryBuilder->leftJoin("root.groups", "groups")->addSelect("groups")
        ->leftJoin("root.securityRoles", "securityRoles")->addSelect("securityRoles");
    }
    return $queryBuilder;
  }

  /**
   * @param string $login
   *
   * @return UserInterface|null
   * @throws NonUniqueResultException
   */
  public function retreiveByLogin(string $login): ?UserInterface
  {
    $query = $this->createQueryBuilder('root')
      ->leftJoin("root.groups", "groups")->addSelect("groups")
      ->leftJoin("root.securityRoles", "securityRoles")->addSelect("securityRoles")
      ->where("root.email = :email OR root.username = :username")
      ->setParameter("email", $login)
      ->setParameter("username", $login)
      ->setMaxResults(1)
      ->getQuery();
    try {
      $object = $query->getSingleResult();
    } catch (NoResultException $e) {
      $object = null;
    }
    return $object;
  }

}
