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
 * Austral Interface Group.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface GroupInterface
{

  /**
   * Add user
   *
   * @param UserInterface $user
   * @return $this
   */
  public function addUser(UserInterface $user): GroupInterface;

  /**
   * Remove users
   *
   * @param UserInterface $user
   *
   * @return $this
   */
  public function removeUser(UserInterface $user): GroupInterface;

  /**
   * Get users
   * @return Collection
   */
  public function getUsers(): Collection;

  /**
   * Add role
   *
   * @param RoleInterface $role
   * @return $this
   */
  public function addRole(RoleInterface $role): GroupInterface;

  /**
   * Remove role
   *
   * @param RoleInterface $role
   *
   * @return $this
   */
  public function removeRole(RoleInterface $role): GroupInterface;

  /**
   * Get role
   * @return Collection
   */
  public function getRoles(): Collection;

  /**
   * Set name
   *
   * @param string $name
   * @return $this
   */
  public function setName(string $name): GroupInterface;

  /**
   * Get name
   *
   * @return string|null
   */
  public function getName(): ?string;

  /**
   * Get keyname
   * @return string|null
   */
  public function getKeyname(): ?string;

  /**
   * @param string|null $keyname
   *
   * @return GroupInterface
   */
  public function setKeyname(string $keyname = null): GroupInterface;

}
