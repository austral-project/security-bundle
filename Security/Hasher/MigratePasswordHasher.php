<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Security\Hasher;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * MigratePasswordHasher.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class MigratePasswordHasher implements PasswordHasherInterface
{
  use CheckPasswordLengthTrait;

  /**
   */
  public function __construct()
  {
  }

  /**
   * hash
   *
   * @param string $plainPassword
   * @param string|null $salt
   *
   * @return string
   */
  public function hash(string $plainPassword, string $salt = null): string
  {
    if ($this->isPasswordTooLong($plainPassword)) {
      throw new InvalidPasswordException();
    }
    $salted = $this->mergePasswordAndSalt($plainPassword, $salt);
    return password_hash($salted,  PASSWORD_BCRYPT);
  }

  /**
   * verify
   *
   * @param string $hashedPassword
   * @param string $plainPassword
   * @param string|null $salt
   *
   * @return bool
   */
  public function verify(string $hashedPassword, string $plainPassword, string $salt = null): bool
  {
    return password_verify($this->mergePasswordAndSalt($plainPassword, $salt), $hashedPassword);
  }

  /**
   * needsRehash
   *
   * @param string $hashedPassword
   *
   * @return bool
   */
  public function needsRehash(string $hashedPassword): bool
  {
    return true;
  }

  /**
   * mergePasswordAndSalt
   *
   * @param string $password
   * @param string|null $salt
   *
   * @return string
   */
  private function mergePasswordAndSalt(string $password, ?string $salt): string
  {
    if (!$salt) {
      return $password;
    }
    return $password.$salt;
  }
}
