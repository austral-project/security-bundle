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

use App\Entity\Austral\SecurityBundle\User;
use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * User provider.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    
  /**
   * @var UserEntityManagerInterface
   */
  protected UserEntityManagerInterface $userManager;

  /**
   * Constructor.
   *
   * @param UserEntityManagerInterface $userManager
   */
  public function __construct(UserEntityManagerInterface $userManager)
  {
    $this->userManager = $userManager;
  }
    
  /**
   * {@inheritDoc}
   */
  public function loadUserByIdentifier($username): UserInterface
  {
    /** @var User $user */
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
   * {@inheritDoc}
   */
  public function loadUserByUsername($username): UserInterface
  {
    /** @var User $user */
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
   * {@inheritDoc}
   */
  public function refreshUser(UserInterface $user): UserInterface
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
  public function supportsClass($class): bool
  {
    $userClass = $this->userManager->getClass();
    return $userClass === $class || is_subclass_of($class, $userClass);
  }

  /**
   * Finds a user by username.
   * This method is meant to be an extension point for child classes.
   *
   * @param string $username
   *
   * @return UserInterface|null
   */
  protected function findUser(string $username): ?UserInterface
  {
    return $this->userManager->retreiveByLogin($username);
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
    $user->setPassword($newHashedPassword);
    $this->userManager->update($user);
  }

}