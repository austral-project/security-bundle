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
use Austral\EntityBundle\EntityManager\EntityManagerORMInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface as AustralUserInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Austral Interface User Manager.
 * @template T of object
 *
 * @extends EntityManagerInterface<T>
 * @extends EntityManagerORMInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface UserEntityManagerInterface extends EntityManagerInterface, EntityManagerORMInterface
{

  /**
  * @param PasswordHasherFactoryInterface $passwordHasherFactory
  *
  * @return $this
  */
  public function setPasswordHasherFactory(PasswordHasherFactoryInterface $passwordHasherFactory): self;

  /**
   * Updates a user.
   *
   * @param UserInterface $user
   *
   * @return $this
   */
  public function updateUser(UserInterface $user): self;

  /**
   * @param UserInterface $user
   *
   * @return $this
   */
  public function updatePassword(UserInterface $user): self;

  /**
   * @param string $email
   *
   * @return AustralUserInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function retrieveByEmail(string $email): ?AustralUserInterface;

  /**
   * @param string $login
   *
   * @return AustralUserInterface<T>|null
   * @throws NonUniqueResultException
   */
  public function retrieveByLogin(string $login): ?AustralUserInterface;
}
