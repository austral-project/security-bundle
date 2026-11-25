<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Password;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Austral Password Updater.
 *
 * Hashes the plain password of a user with the hasher configured for its class (security.password_hashers),
 * then clears the plain password and the pending password reset.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PasswordUpdater
{
  /**
   * @param UserPasswordHasherInterface $passwordHasher
   */
  public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
  {
  }

  /**
   * @param UserInterface $user
   *
   * @return bool True when the password changed
   */
  public function update(UserInterface $user): bool
  {
    $plainPassword = $user->getPlainPassword();
    if (null === $plainPassword || '' === $plainPassword)
    {
      return false;
    }
    $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
    $user->setPlainPassword(null);
    $user->clearPasswordReset();

    return true;
  }
}
