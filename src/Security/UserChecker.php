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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * Austral User Checker: a disabled user cannot log in (password, login link, remember me).
 *
 * Set it on the firewalls: user_checker: austral.security.user_checker.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class UserChecker implements UserCheckerInterface
{
  /**
   * @param SymfonyUserInterface $user
   *
   * @return void
   */
  public function checkPreAuth(SymfonyUserInterface $user): void
  {
    if ($user instanceof UserInterface && !$user->getIsActive())
    {
      $exception = new DisabledException('Account is disabled.');
      $exception->setUser($user);

      throw $exception;
    }
  }

  /**
   * @param SymfonyUserInterface $user
   * @param TokenInterface|null  $token
   *
   * @return void
   */
  public function checkPostAuth(SymfonyUserInterface $user, ?TokenInterface $token = null): void
  {
  }
}
