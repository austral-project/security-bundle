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

use Austral\EntityBundle\ORM\AustralQueryBuilder;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;

use Austral\EntityBundle\Repository\EntityRepository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Austral Base User Repository.
 * @template T of object
 *
 * @extends EntityRepository<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
abstract class BaseUserEntityRepository extends EntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{

  /**
   * @param string $username
   * @param \Closure(object): mixed|null $closure
   *
   * @return UserInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function retrieveByUsername(string $username, ?\Closure $closure = null): ?UserInterface
  {
    /** @var UserInterface<T> $object */
    $object =  $this->retrieveByKey("username", $username, $closure);
    return $object;
  }

  /**
   * @param string $username
   * @param \Closure(object): mixed|null $closure
   *
   * @return UserInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function retrieveByEmail(string $username, ?\Closure $closure = null): ?UserInterface
  {
    /** @var UserInterface<T> $object */
    $object =  $this->retrieveByKey("email", $username, $closure);
    return $object;
  }

  /**
   * @param $name
   * @param AustralQueryBuilder<T> $queryBuilder
   *
   * @return AustralQueryBuilder<T>
   */
  public function queryBuilderExtends($name, AustralQueryBuilder $queryBuilder): AustralQueryBuilder
  {
    if($name == "retrieve-by-key")
    {
      $queryBuilder->leftJoin("root.groups", "groups")->addSelect("groups")
        ->leftJoin("root.securityRoles", "securityRoles")->addSelect("securityRoles");
    }
    return $queryBuilder;
  }

  /**
   * loadUserByIdentifier
   *
   * @param string $identifier
   *
   * @return UserInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function loadUserByIdentifier(string $identifier): ?UserInterface
  {
    return $this->retrieveByLogin($identifier);
  }

  /**
   * @param string $login
   *
   * @return UserInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function retrieveByLogin(string $login): ?UserInterface
  {
    try {
      $queryBuilder = $this->createQueryBuilder('root')
        ->leftJoin("root.groups", "groups")->addSelect("groups")
        ->leftJoin("root.securityRoles", "securityRoles")->addSelect("securityRoles")
        ->where("root.email = :email OR root.username = :username")
        ->setParameter("email", $login)
        ->setParameter("username", $login)
        ->setMaxResults(1);

      $paginatorResult = $this->paginatorByQueryBuilder($queryBuilder);
      /** @var UserInterface<T> $object */
      $object = $paginatorResult->first();
    } catch (NoResultException|\Exception $e) {
      $object = null;
    }
    return $object;
  }

  /**
   * upgradePassword
   *
   * @param UserInterface|PasswordAuthenticatedUserInterface $user
   * @param string $newHashedPassword
   *
   * @return void
   */
  public function upgradePassword(UserInterface|\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
  {
    if($user instanceof UserInterface)
    {
      // set the new hashed password on the User object
      $user->setPassword($newHashedPassword);
      $this->getEntityManager()->flush();
    }
  }

}
