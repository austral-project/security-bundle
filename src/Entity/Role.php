<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Entity;

use Austral\EntityBundle\Entity\Entity;
use Austral\EntityBundle\Entity\Traits\EntityTimestampableTrait;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Austral Role.
 *
 * A permission: a Symfony role ("ROLE_...") with a name.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends Entity<object>
 */
#[ORM\MappedSuperclass]
abstract class Role extends Entity implements RoleInterface
{
  use EntityTimestampableTrait;

  #[ORM\Column(name: 'id', type: Types::STRING, length: 40)]
  #[ORM\Id]
  protected string $id;

  /**
   * @var Collection<int, UserInterface>
   */
  #[ORM\ManyToMany(targetEntity: UserInterface::class, mappedBy: 'securityRoles', cascade: ['persist'])]
  protected Collection $users;

  /**
   * @var Collection<int, GroupInterface>
   */
  #[ORM\ManyToMany(targetEntity: GroupInterface::class, mappedBy: 'roles', cascade: ['persist'])]
  protected Collection $groups;

  #[ORM\Column(name: 'name', type: Types::STRING, length: 255, unique: true)]
  protected string $name = '';

  #[ORM\Column(name: 'role', type: Types::STRING, length: 255, unique: true)]
  protected string $role = '';

  public function __construct()
  {
    parent::__construct();
    $this->id = Uuid::v4()->toRfc4122();
    $this->users = new ArrayCollection();
    $this->groups = new ArrayCollection();
  }

  public function __toString(): string
  {
    return $this->name;
  }

  /**
   * @return array<string, mixed>
   */
  public function arrayObject(): array
  {
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'role' => $this->getRole(),
      'created' => $this->getCreated()->format('Y-m-d'),
    ];
  }

  public function addUser(UserInterface $user): self
  {
    if (!$this->users->contains($user))
    {
      $this->users->add($user);
      $user->addSecurityRole($this);
    }

    return $this;
  }

  public function removeUser(UserInterface $user): self
  {
    if ($this->users->removeElement($user))
    {
      $user->removeSecurityRole($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, UserInterface>
   */
  public function getUsers(): Collection
  {
    return $this->users;
  }

  public function addGroup(GroupInterface $group): self
  {
    if (!$this->groups->contains($group))
    {
      $this->groups->add($group);
      $group->addRole($this);
    }

    return $this;
  }

  public function removeGroup(GroupInterface $group): self
  {
    if ($this->groups->removeElement($group))
    {
      $group->removeRole($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, GroupInterface>
   */
  public function getGroups(): Collection
  {
    return $this->groups;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setRole(string $role): self
  {
    $this->role = $role;

    return $this;
  }

  public function getRole(): string
  {
    return $this->role;
  }
}
