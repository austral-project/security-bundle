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
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Austral User Repository.
 *
 * The users are loaded with their groups, the roles of their groups and their roles: getRoles() needs no
 * other query.
 *
 * @extends EntityRepository<UserInterface>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserRepository extends EntityRepository
{
  /**
   * The user whose email (case insensitive) or username is the login; the email wins when two users match.
   *
   * @param string $login
   *
   * @return UserInterface|null
   */
  public function retrieveByLogin(string $login): ?UserInterface
  {
    if ('' === $login)
    {
      return null;
    }
    $users = $this->userQueryBuilder()
      ->where('LOWER(root.email) = :email OR root.username = :username')
      ->setParameter('email', mb_strtolower($login))
      ->setParameter('username', $login)
      ->getQuery()
      ->getResult();
    $found = null;
    foreach ($users as $user)
    {
      if ($user instanceof UserInterface)
      {
        if (mb_strtolower($user->getEmail()) === mb_strtolower($login))
        {
          return $user;
        }
        $found ??= $user;
      }
    }

    return $found;
  }

  /**
   * @param string $email Compared case insensitive
   *
   * @return UserInterface|null
   */
  public function retrieveByEmail(string $email): ?UserInterface
  {
    return $this->first($this->userQueryBuilder()
      ->where('LOWER(root.email) = :email')
      ->setParameter('email', mb_strtolower($email)));
  }

  /**
   * @param string $id
   *
   * @return UserInterface|null
   */
  public function retrieveUserById(string $id): ?UserInterface
  {
    return $this->first($this->userQueryBuilder()
      ->where('root.id = :id')
      ->setParameter('id', $id));
  }

  /**
   * @param string $selector
   *
   * @return UserInterface|null
   */
  public function retrieveByPasswordResetSelector(string $selector): ?UserInterface
  {
    return $this->first($this->userQueryBuilder()
      ->where('root.passwordResetSelector = :selector')
      ->setParameter('selector', $selector));
  }

  /**
   * @return QueryBuilder
   */
  private function userQueryBuilder(): QueryBuilder
  {
    return $this->createQueryBuilder('root')
      ->leftJoin('root.groups', 'groups')->addSelect('groups')
      ->leftJoin('groups.roles', 'groupRoles')->addSelect('groupRoles')
      ->leftJoin('root.securityRoles', 'securityRoles')->addSelect('securityRoles');
  }

  /**
   * @param QueryBuilder $queryBuilder
   *
   * @return UserInterface|null
   */
  private function first(QueryBuilder $queryBuilder): ?UserInterface
  {
    foreach ($queryBuilder->getQuery()->getResult() as $user)
    {
      if ($user instanceof UserInterface)
      {
        return $user;
      }
    }

    return null;
  }
}
