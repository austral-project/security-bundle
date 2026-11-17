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
 * Austral Interface Group.
 * @template T of object
 *
 * @extends EntityInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface GroupInterface extends EntityInterface
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
   * @param RoleInterface<T> $role
   *
   * @return $this
   */
  public function addRole(RoleInterface $role): self;

  /**
   * @param RoleInterface<T> $role
   *
   * @return $this
   */
  public function removeRole(RoleInterface $role): self;

  /**
   * @return Collection<int|string, RoleInterface<T>>
   */
  public function getRoles(): Collection;

  /**
   * @return list<string>
   */
  public function getRolesArray(): array;

  /**
   * @param string $name
   * @return $this
   */
  public function setName(string $name): self;

  /**
   * @return string
   */
  public function getName(): string;

  /**
   * @return string
   */
  public function getKeyname(): string;

  /**
   * @param string $keyname
   *
   * @return $this
   */
  public function setKeyname(string $keyname): self;

}
