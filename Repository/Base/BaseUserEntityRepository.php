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
use Austral\EntityBundle\Repository\EntityRepositoryInterface;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Austral Base User Repository.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
class BaseUserEntityRepository extends EntityRepository implements EntityRepositoryInterface, UserLoaderInterface, PasswordUpgraderInterface
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
   * @throws NonUniqueResultException
   * @deprecated since Symfony 5.3
   */
  public function loadUserByUsername(string $usernameOrEmail): ?UserInterface
  {
    return $this->loadUserByIdentifier($usernameOrEmail);
  }

  /**
   * loadUserByIdentifier
   *
   * @param string $usernameOrEmail
   *
   * @return UserInterface|null
   * @throws NonUniqueResultException
   */
  public function loadUserByIdentifier(string $usernameOrEmail): ?UserInterface
  {
    return $this->retreiveByLogin($usernameOrEmail);
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

  /**
   * upgradePassword
   *
   * @param UserInterface $user
   * @param string $newHashedPassword
   *
   * @return void
   */
  public function upgradePassword(UserInterface $user, string $newHashedPassword): void
  {
    // set the new hashed password on the User object
    $user->setPassword($newHashedPassword);

    // execute the queries on the database
    $this->getEntityManager()->flush();
  }

}
