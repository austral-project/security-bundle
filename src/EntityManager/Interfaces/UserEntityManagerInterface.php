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

use Austral\EntityBundle\EntityManager\EntityManagerORMInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;

/**
 * Austral User Entity Manager Interface.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends EntityManagerORMInterface<object>
 */
interface UserEntityManagerInterface extends EntityManagerORMInterface
{
  /**
   * @param array<string, mixed> $values
   *
   * @return UserInterface
   */
  public function create(array $values = []): UserInterface;

  /**
   * retrieveByEmail
   *
   * @param string $email
   *
   * @return UserInterface|null
   */
  public function retrieveByEmail(string $email): ?UserInterface;

  /**
   * retrieveByLogin
   *
   * @param string $login
   *
   * @return UserInterface|null
   */
  public function retrieveByLogin(string $login): ?UserInterface;

  /**
   * retrieveUserById
   *
   * @param string $id
   *
   * @return UserInterface|null
   */
  public function retrieveUserById(string $id): ?UserInterface;

  /**
   * retrieveByPasswordResetSelector
   *
   * @param string $selector
   *
   * @return UserInterface|null
   */
  public function retrieveByPasswordResetSelector(string $selector): ?UserInterface;

  /**
   * Saves the user: its plain password, when given, is hashed by the PasswordListener.
   */
  public function updateUser(UserInterface $user, bool $andFlush = true): self;
}
