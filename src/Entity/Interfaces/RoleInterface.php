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
 * Austral Role Interface.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends EntityInterface<object>
 */
interface RoleInterface extends EntityInterface
{
  /**
   * addUser
   *
   * @param UserInterface $user
   *
   * @return self
   */
  public function addUser(UserInterface $user): self;

  /**
   * removeUser
   *
   * @param UserInterface $user
   *
   * @return self
   */
  public function removeUser(UserInterface $user): self;

  /**
   * @return Collection<int, UserInterface>
   */
  public function getUsers(): Collection;

  /**
   * addGroup
   *
   * @param GroupInterface $group
   *
   * @return self
   */
  public function addGroup(GroupInterface $group): self;

  /**
   * removeGroup
   *
   * @param GroupInterface $group
   *
   * @return self
   */
  public function removeGroup(GroupInterface $group): self;

  /**
   * @return Collection<int, GroupInterface>
   */
  public function getGroups(): Collection;

  /**
   * setName
   *
   * @param string $name
   *
   * @return self
   */
  public function setName(string $name): self;

  /**
   * getName
   *
   * @return string
   */
  public function getName(): string;

  /**
   * setRole
   *
   * @param string $role
   *
   * @return self
   */
  public function setRole(string $role): self;

  /**
   * getRole
   *
   * @return string
   */
  public function getRole(): string;
}
