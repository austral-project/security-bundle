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

use Austral\EntityBundle\EntityManager\EntityManager;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;
use Austral\SecurityBundle\Repository\UserRepository;

/**
 * Austral User Entity Manager.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends EntityManager<object>
 */
class UserEntityManager extends EntityManager implements UserEntityManagerInterface
{
  /**
   * @param string|null $entity
   *
   * @return UserRepository|null
   */
  public function getRepository(?string $entity = null): ?UserRepository
  {
    return $this->repository instanceof UserRepository ? $this->repository : null;
  }

  /**
   * @param array<string, mixed> $values
   *
   * @return UserInterface
   */
  public function create(array $values = []): UserInterface
  {
    $user = parent::create($values);
    if (!$user instanceof UserInterface)
    {
      throw new \LogicException(\sprintf('The user class must implement "%s".', UserInterface::class));
    }

    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveByEmail(string $email): ?UserInterface
  {
    return $this->userRepository()->retrieveByEmail($email);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveByLogin(string $login): ?UserInterface
  {
    return $this->userRepository()->retrieveByLogin($login);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveUserById(string $id): ?UserInterface
  {
    return $this->userRepository()->retrieveUserById($id);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveByPasswordResetSelector(string $selector): ?UserInterface
  {
    return $this->userRepository()->retrieveByPasswordResetSelector($selector);
  }

  /**
   * {@inheritdoc}
   */
  public function updateUser(UserInterface $user, bool $andFlush = true): self
  {
    $this->update($user, $andFlush);

    return $this;
  }

  /**
   * @return UserRepository
   */
  private function userRepository(): UserRepository
  {
    return $this->getRepository() ?? throw new \LogicException(\sprintf('The repository of the user class "%s" must extend "%s".', $this->getClass(), UserRepository::class));
  }
}
