<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\EntityManager\Interfaces;

use Austral\EntityBundle\EntityManager\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Austral Interface User Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface UserEntityManagerInterface extends EntityManagerInterface
{

  /**
  * @param PasswordHasherFactoryInterface $passwordHasherFactory
  *
  * @return $this
  */
  public function setPasswordHasherFactory(PasswordHasherFactoryInterface $passwordHasherFactory): UserEntityManagerInterface;

  /**
   * Updates a user.
   *
   * @param UserInterface $user
   *
   * @return $this
   */
  public function updateUser(UserInterface $user): UserEntityManagerInterface;

  /**
   * Updates a user password if a plain password is set.
   *
   * @param UserInterface $user
   *
   * @return $this
   */
  public function updatePassword(UserInterface $user): UserEntityManagerInterface;

  /**
   * Retreive User with the email
   *
   * @param string $email
   *
   * @return mixed
   */
  public function retreiveByEmail(string $email);

  /**
   * Retreive User with the login
   *
   * @param string $login
   *
   * @return mixed
   */
  public function retreiveByLogin(string $login);
}
