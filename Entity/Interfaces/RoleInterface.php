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

use Doctrine\Common\Collections\Collection;

/**
 * Austral Interface Role.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface RoleInterface
{

  /**
   * Add user
   *
   * @param UserInterface $user
   * @return $this
   */
  public function addUser(UserInterface $user): RoleInterface;

  /**
   * Remove users
   *
   * @param UserInterface $user
   *
   * @return $this
   */
  public function removeUser(UserInterface $user): RoleInterface;

  /**
   * Get users
   *
   * @return Collection
   */
  public function getUsers(): Collection;

  /**
   * Add group
   *
   * @param GroupInterface $group
   * @return $this
   */
  public function addGroup(GroupInterface $group): RoleInterface;

  /**
   * Remove group
   *
   * @param GroupInterface $group
   *
   * @return $this
   */
  public function removeGroup(GroupInterface $group): RoleInterface;

  /**
   * Get groups
   *
   * @return Collection
   */
  public function getGroups(): Collection;

  /**
   * Set name
   *
   * @param string $name
   *
   * @return $this
   */
  public function setName(string $name): RoleInterface;

  /**
   * Get name
   *
   * @return string|null
   */
  public function getName(): ?string;

  /**
   * Set role
   *
   * @param string $role
   *
   * @return $this
   */
  public function setRole(string $role): RoleInterface;

  /**
   * Get role
   *
   * @return string|null
   */
  public function getRole(): ?string;

}
