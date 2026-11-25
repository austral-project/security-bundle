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
 * Austral Group.
 *
 * A set of roles given to its users.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends Entity<object>
 */
#[ORM\MappedSuperclass]
abstract class Group extends Entity implements GroupInterface
{
  use EntityTimestampableTrait;

  /**
   * @var string
   */
  #[ORM\Column(name: 'id', type: Types::STRING, length: 40)]
  #[ORM\Id]
  protected string $id;

  /**
   * @var Collection<int, UserInterface>
   */
  #[ORM\ManyToMany(targetEntity: UserInterface::class, mappedBy: 'groups', cascade: ['persist'])]
  protected Collection $users;

  /**
   * @var Collection<int, RoleInterface>
   */
  #[ORM\ManyToMany(targetEntity: RoleInterface::class, inversedBy: 'groups', cascade: ['persist'])]
  #[ORM\JoinTable(name: 'austral_security_role_group')]
  #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  protected Collection $roles;

  /**
   * @var string
   */
  #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
  protected string $name = '';

  /**
   * @var string
   */
  #[ORM\Column(name: 'keyname', type: Types::STRING, length: 255)]
  protected string $keyname = '';

  /**
   * Group constructor
   */
  public function __construct()
  {
    parent::__construct();
    $this->id = Uuid::v4()->toRfc4122();
    $this->users = new ArrayCollection();
    $this->roles = new ArrayCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function __toString(): string
  {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function arrayObject(): array
  {
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'created' => $this->getCreated()->format('Y-m-d'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function addUser(UserInterface $user): self
  {
    if (!$this->users->contains($user))
    {
      $this->users->add($user);
      $user->addGroup($this);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeUser(UserInterface $user): self
  {
    if ($this->users->removeElement($user))
    {
      $user->removeGroup($this);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsers(): Collection
  {
    return $this->users;
  }

  /**
   * {@inheritdoc}
   */
  public function addRole(RoleInterface $role): self
  {
    if (!$this->roles->contains($role))
    {
      $this->roles->add($role);
      $role->addGroup($this);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeRole(RoleInterface $role): self
  {
    if ($this->roles->removeElement($role))
    {
      $role->removeGroup($this);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoles(): Collection
  {
    return $this->roles;
  }

  /**
   * {@inheritdoc}
   */
  public function getRolesArray(): array
  {
    $roles = [];
    foreach ($this->roles as $role)
    {
      $roles[] = $role->getRole();
    }

    return $roles;
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeyname(): string
  {
    return $this->keyname;
  }

  /**
   * {@inheritdoc}
   */
  public function setKeyname(string $keyname): self
  {
    $this->keyname = $keyname;

    return $this;
  }
}
