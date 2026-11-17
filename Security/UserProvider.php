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

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * User provider.
 * @template T of object
 * @template TUser of UserInterface
 * @implements UserProviderInterface<TUser>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    
  /**
   * @var UserEntityManagerInterface<T>
   */
  protected UserEntityManagerInterface $userManager;

  /**
   * Constructor.
   *
   * @param UserEntityManagerInterface<T> $userManager
   */
  public function __construct(UserEntityManagerInterface $userManager)
  {
    $this->userManager = $userManager;
  }

  /**
   * @param string $identifier
   * @return UserInterface<T>
   */
  public function loadUserByIdentifier(string $identifier): UserInterface
  {
    /** @var UserInterface<T>|null $user */
    $user = $this->findUser($identifier);
    if (!$user)
    {
      throw new UnsupportedUserException(sprintf('Username "%s" does not exist.', $identifier));
    }
    if (!$user->getIsActive())
    {
      throw new UnsupportedUserException(sprintf('Username "%s" is not actif.', $identifier));
    }
    return $user;
  }

  /**
   * @param string $username
   * @return UserInterface<T>
   */
  public function loadUserByUsername(string $username): UserInterface
  {
    /** @var UserInterface<T>|null $user */
    $user = $this->findUser($username);
    if (!$user)
    {
      throw new UnsupportedUserException(sprintf('Username "%s" does not exist.', $username));
    }
    if (!$user->getIsActive())
    {
      throw new UnsupportedUserException(sprintf('Username "%s" is not actif.', $username));
    }
    return $user;
  }

  /**
   * refreshUser
   *
   * @param \Symfony\Component\Security\Core\User\UserInterface $user
   * @return UserInterface<T>
   */
  public function refreshUser( \Symfony\Component\Security\Core\User\UserInterface $user): UserInterface
  {
    if (!$user instanceof UserInterface)
    {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
    }
    return $this->loadUserByIdentifier($user->getUsername());
  }

  /**
   * {@inheritDoc}
   */
  public function supportsClass(string $class): bool
  {
    $userClass = $this->userManager->getClass() ?? UserInterface::class;
    return $userClass === $class || is_subclass_of($class, $userClass);
  }

  /**
   * @param string $username
   *
   * @return UserInterface<T>|null
   */
  protected function findUser(string $username): ?UserInterface
  {
    return $this->userManager->retrieveByLogin($username);
  }

  /**
   * upgradePassword
   *
   * @param PasswordAuthenticatedUserInterface $user
   * @param string $newHashedPassword
   *
   * @return void
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
  {
    if($user instanceof UserInterface)
    {
      /** @var UserInterface<T> $userEntity */
      $userEntity = $user;
      $userEntity->setPassword($newHashedPassword);
      $this->userManager->update($userEntity);
    }
  }

}