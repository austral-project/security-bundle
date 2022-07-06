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

use Austral\EntityBundle\Entity\EntityInterface;
use Austral\EntityBundle\EntityManager\EntityManager;

use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Austral User Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class UserEntityManager extends EntityManager implements UserEntityManagerInterface
{

  /**
   * @var UserRepository
   */
  protected $repository;

  /**
   * @var PasswordHasherFactoryInterface
   */
  protected PasswordHasherFactoryInterface $passwordHasherFactory;


  /**
   * @param PasswordHasherFactoryInterface $passwordHasherFactory
   *
   * @return $this
   */
  public function setPasswordHasherFactory(PasswordHasherFactoryInterface $passwordHasherFactory): UserEntityManager
  {
    $this->passwordHasherFactory = $passwordHasherFactory;
    return $this;
  }

  /**
   * @param string $email
   *
   * @return UserInterface|null
   * @throws NonUniqueResultException
   */
  public function retreiveByEmail(string $email): ?UserInterface
  {
    return $this->repository->retreiveByEmail($email);
  }

  /**
   * @param string $login
   *
   * @return UserInterface|null
   * @throws NonUniqueResultException
   */
  public function retreiveByLogin(string $login): ?UserInterface
  {
    return $this->repository->retreiveByLogin($login);
  }

  /**
   * @param UserInterface|EntityInterface $user
   *
   * @return UserEntityManager
   */
  public function deleteUser(UserInterface $user): UserEntityManager
  {
    return $this->delete($user);
  }

  /**
   * Updates a user.
   *
   * @param UserInterface|EntityInterface|AustralUserInterface $user
   * @param bool $andFlush Whether to flush the changes (default true)
   *
   * @return $this
   */
  public function updateUser(UserInterface $user, bool $andFlush = true): UserEntityManager
  {
    $this->updatePassword($user);
    return parent::update($user, $andFlush);
  }

  /**
   * @param array $values
   *
   * @return UserInterface
   * @throws \Exception
   */
  public function create(array $values = array()): UserInterface
  {
    /** @var UserInterface|EntityInterface|AustralUserInterface $object */
    $object = parent::create($values);
    $object->hydrate($values);
    $object->setSalt(bin2hex(random_bytes(20)));
    return $object;
  }

  /**
   * @param UserInterface|EntityInterface|AustralUserInterface $user
   *
   * @return UserEntityManager
   */
  public function updatePassword(UserInterface $user): UserEntityManager
  {
    if (0 !== strlen($password = $user->getPlainPassword()))
    {
      $encoder = $this->getEncoder($user);
      $user->setPassword($encoder->hash($password.$user->getSalt()));
      $user->eraseCredentials();
    }
    return $this;
  }

  /**
   * @param UserInterface $user
   *
   * @return PasswordHasherInterface
   */
  protected function getEncoder(UserInterface $user): PasswordHasherInterface
  {
    return $this->passwordHasherFactory->getPasswordHasher($user);
  }

}
