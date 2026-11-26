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
  public function addUser(UserInterface $user): self;

  public function removeUser(UserInterface $user): self;

  /**
   * @return Collection<int, UserInterface>
   */
  public function getUsers(): Collection;

  public function addGroup(GroupInterface $group): self;

  public function removeGroup(GroupInterface $group): self;

  /**
   * @return Collection<int, GroupInterface>
   */
  public function getGroups(): Collection;

  public function setName(string $name): self;

  public function getName(): string;

  public function setRole(string $role): self;

  public function getRole(): string;
}
