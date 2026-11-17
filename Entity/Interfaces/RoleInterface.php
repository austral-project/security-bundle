<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Entity\Interfaces;

use Austral\Contracts\Entity\EntityInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Austral Interface Role.
 * @template T of object
 *
 * @extends EntityInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface RoleInterface extends EntityInterface
{

  /**
   * @param UserInterface<T> $user
   * @return $this
   */
  public function addUser(UserInterface $user): self;

  /**
   * @param UserInterface<T> $user
   *
   * @return $this
   */
  public function removeUser(UserInterface $user): self;

  /**
   * @return Collection<int|string, UserInterface<T>>
   */
  public function getUsers(): Collection;

  /**
   * @param GroupInterface<T> $group
   *
   * @return $this
   */
  public function addGroup(GroupInterface $group): self;

  /**
   * Remove group
   *
   * @param GroupInterface<T> $group
   *
   * @return $this
   */
  public function removeGroup(GroupInterface $group): self;

  /**
   * Get groups
   *
   * @return Collection<int|string, GroupInterface<T>>
   */
  public function getGroups(): Collection;

  /**
   * Set name
   *
   * @param string $name
   *
   * @return $this
   */
  public function setName(string $name): self;

  /**
   * Get name
   *
   * @return string
   */
  public function getName(): string;

  /**
   * Set role
   *
   * @param string $role
   *
   * @return $this
   */
  public function setRole(string $role): self;

  /**
   * Get role
   *
   * @return string
   */
  public function getRole(): string;

}
