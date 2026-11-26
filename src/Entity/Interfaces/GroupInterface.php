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
  public function addUser(UserInterface $user): self;

  public function removeUser(UserInterface $user): self;

  /**
   * @return Collection<int, UserInterface>
   */
  public function getUsers(): Collection;

  public function addRole(RoleInterface $role): self;

  public function removeRole(RoleInterface $role): self;

  /**
   * @return Collection<int, RoleInterface>
   */
  public function getRoles(): Collection;

  /**
   * @return list<string>
   */
  public function getRolesArray(): array;

  public function setName(string $name): self;

  public function getName(): string;

  public function getKeyname(): string;

  public function setKeyname(string $keyname): self;
}
