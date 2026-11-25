<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Security;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Austral User Provider.
 *
 * Loads a user by its email (case insensitive), its username or its id (the identifier of a user without
 * username), and reloads it by its id. A disabled user is refused by the UserChecker at login and logged
 * out by User::isEqualTo() once logged in.
 *
 * @implements UserProviderInterface<UserInterface>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
  /**
   * @param UserEntityManagerInterface $users
   */
  public function __construct(private readonly UserEntityManagerInterface $users)
  {
  }

  /**
   * @param string $identifier
   *
   * @return UserInterface
   */
  public function loadUserByIdentifier(string $identifier): UserInterface
  {
    $user = $this->users->retrieveByLogin($identifier) ?? $this->users->retrieveUserById($identifier);
    if (null === $user)
    {
      $exception = new UserNotFoundException(\sprintf('No user "%s".', $identifier));
      $exception->setUserIdentifier($identifier);

      throw $exception;
    }

    return $user;
  }

  /**
   * @param SymfonyUserInterface $user
   *
   * @return UserInterface
   */
  public function refreshUser(SymfonyUserInterface $user): UserInterface
  {
    if (!$user instanceof UserInterface || !$this->supportsClass($user::class))
    {
      throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
    }
    $refreshed = $this->users->retrieveUserById($user->getId());
    if (null === $refreshed)
    {
      $exception = new UserNotFoundException(\sprintf('The user "%s" no longer exists.', $user->getId()));
      // Not getUserIdentifier(): a user of an old session can have neither id nor username.
      $exception->setUserIdentifier($user->getId());

      throw $exception;
    }

    return $refreshed;
  }

  /**
   * @param string $class
   *
   * @return bool
   */
  public function supportsClass(string $class): bool
  {
    return is_a($class, $this->users->getClass() ?? UserInterface::class, true);
  }

  /**
   * Called after a login when the hasher of the user changed (password_hashers migrate_from, cost...).
   *
   * @param PasswordAuthenticatedUserInterface $user
   * @param string                             $newHashedPassword
   *
   * @return void
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
  {
    if ($user instanceof UserInterface)
    {
      $user->setPassword($newHashedPassword);
      $this->users->updateUser($user);
    }
  }
}
