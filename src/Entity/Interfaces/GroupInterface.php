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
 * Austral Group Interface.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends EntityInterface<object>
 */
interface GroupInterface extends EntityInterface
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
   * addRole
   *
   * @param RoleInterface $role
   *
   * @return self
   */
  public function addRole(RoleInterface $role): self;

  /**
   * removeRole
   *
   * @param RoleInterface $role
   *
   * @return self
   */
  public function removeRole(RoleInterface $role): self;

  /**
   * @return Collection<int, RoleInterface>
   */
  public function getRoles(): Collection;

  /**
   * @return list<string>
   */
  public function getRolesArray(): array;

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
   * getKeyname
   *
   * @return string
   */
  public function getKeyname(): string;

  /**
   * setKeyname
   *
   * @param string $keyname
   *
   * @return self
   */
  public function setKeyname(string $keyname): self;
}
