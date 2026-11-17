<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\EntityManager;

use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;
use Austral\SecurityBundle\Repository\UserRepository;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface as AustralUserInterface;

use Austral\EntityBundle\EntityManager\EntityManager;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Austral User Manager.
 * @template T of object
 *
 * @extends EntityManager<T>
 * @implements UserEntityManagerInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class UserEntityManager extends EntityManager implements UserEntityManagerInterface
{

  /**
   * @param class-string<T>|null $entity
   * @return UserRepository<T>|null
   */
  public function getRepository(?string $entity = null): ?UserRepository
  {
    /** @var UserRepository<T>|null $repository */
    $repository = $this->repository;
    return $repository;
  }

  /**
   * @param array<string, mixed> $values
   *
   * @return AustralUserInterface<T>
   */
  public function create(array $values = array()): AustralUserInterface
  {
    /** @var AustralUserInterface<T> $object */
    $object = parent::create($values);
    return $object;
  }

  /**
   * @var PasswordHasherFactoryInterface|null
   */
  protected ?PasswordHasherFactoryInterface $passwordHasherFactory = null;


  /**
   * @param PasswordHasherFactoryInterface $passwordHasherFactory
   *
   * @return $this
   */
  public function setPasswordHasherFactory(PasswordHasherFactoryInterface $passwordHasherFactory): self
  {
    $this->passwordHasherFactory = $passwordHasherFactory;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function retrieveByEmail(string $email): ?AustralUserInterface
  {
    return $this->getRepository()?->retrieveByEmail($email) ?? null;
  }

  /**
   * @inheritDoc
   */
  public function retrieveByLogin(string $login): ?AustralUserInterface
  {
    return $this->getRepository()?->retrieveByLogin($login) ?? null;
  }

  /**
   * @param AustralUserInterface<T> $user
   *
   * @return $this
   */
  public function deleteUser(AustralUserInterface $user): self
  {
    return $this->delete($user);
  }

  /**
   * @param AustralUserInterface<T> $user
   * @param bool $andFlush Whether to flush the changes (default true)
   *
   * @return $this
   */
  public function updateUser(UserInterface $user, bool $andFlush = true): self
  {
    $this->updatePassword($user);
    return parent::update($user, $andFlush);
  }

  /**
   * @param AustralUserInterface<T> $user
   *
   * @return $this
   */
  public function updatePassword(UserInterface $user): self
  {
    $encoder = $this->getEncoder($user);
    if($encoder && $user->getPlainPassword() && (0 !== strlen($password = $user->getPlainPassword())))
    {
      $user->setPassword($encoder->hash($password."@".$user->getSalt()));
    }
    return $this;
  }

  /**
   * @param PasswordAuthenticatedUserInterface $user
   *
   * @return PasswordHasherInterface|null
   */
  protected function getEncoder(PasswordAuthenticatedUserInterface $user): ?PasswordHasherInterface
  {
    return $this->passwordHasherFactory?->getPasswordHasher($user);
  }

}
