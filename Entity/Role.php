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

use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\EntityBundle\Entity\Entity;
use Austral\EntityBundle\Entity\EntityInterface;
use Austral\EntityBundle\Entity\Traits as Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Austral Role Entity.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 * @ORM\MappedSuperclass
 */
abstract class Role extends Entity implements RoleInterface, EntityInterface
{

  use Traits\EntityTimestampableTrait;

  /**
   * @var string
   * @ORM\Column(name="id", type="string", length=40)
   * @ORM\Id
   */
  protected $id;

  /**
   * @ORM\ManyToMany(targetEntity="\Austral\SecurityBundle\Entity\Interfaces\UserInterface", mappedBy="securityRoles", cascade={"persist", "remove"})
   */
  protected Collection $users;
  
  /**
   * @ORM\ManyToMany(targetEntity="\Austral\SecurityBundle\Entity\Interfaces\GroupInterface", mappedBy="roles", cascade={"persist", "remove"})
   */
  protected Collection $groups;

  /**
   * @var string|null
   * @ORM\Column(name="name", type="string", length=255, nullable=false )
   */
  protected ?string $name=null;

  /**
   * @var string|null
   * @ORM\Column(name="role", type="string", length=255, nullable=false )
   */
  protected ?string $role=null;

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Role constructor.
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();
    $this->users = new ArrayCollection();
    $this->groups = new ArrayCollection();
    $this->id = Uuid::uuid4()->toString();
  }

  /**
   * @return array
   */
  public function arrayObject(): array
  {
    return array(
      "id"              =>  $this->getId(),
      "name"            =>  $this->getName(),
      "role"            =>  $this->getRole(),
      "created"         =>  $this->getCreated()->format("Y-m-d"),
    );
  }

  /**
   * Add user
   *
   * @param UserInterface $user
   * @return $this
   */
  public function addUser(UserInterface $user): Role
  {
    if(!$this->users->contains($user))
    {
      $this->users[] = $user;
      $user->addSecurityRole($this);
    }
    return $this;
  }

  /**
   * Remove users
   *
   * @param UserInterface $user
   *
   * @return $this
   */
  public function removeUser(UserInterface $user): Role
  {
    if($this->users->contains($user))
    {
      $this->users[] = $user;
      $user->removeSecurityRole($this);
    }
    return $this;
  }

  /**
   * Get users
   *
   * @return Collection
   */
  public function getUsers(): Collection
  {
    return $this->users;
  }

  /**
   * Add group
   *
   * @param GroupInterface $group
   * @return $this
   */
  public function addGroup(GroupInterface $group): Role
  {
    if(!$this->groups->contains($group))
    {
      $this->groups[] = $group;
      $group->addRole($this);
    }
    return $this;
  }

  /**
   * Remove group
   *
   * @param GroupInterface $group
   *
   * @return $this
   */
  public function removeGroup(GroupInterface $group): Role
  {
    if($this->groups->contains($group))
    {
      $this->groups->removeElement($group);
      $group->removeRole($this);
    }
    return $this;
  }

  /**
   * Get groups
   *
   * @return Collection
   */
  public function getGroups(): Collection
  {
    return $this->groups;
  }
  
  /**
   * Set name
   *
   * @param string $name
   *
   * @return $this
   */
  public function setName(string $name): Role
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get name
   *
   * @return string|null
   */
  public function getName(): ?string
  {
    return $this->name;
  }
    
  /**
   * Set role
   *
   * @param string $role
   *
   * @return $this
   */
  public function setRole(string $role): Role
  {
    $this->role = $role;
    return $this;
  }

  /**
   * Get role
   *
   * @return string|null
   */
  public function getRole(): ?string
  {
    return $this->role;
  }

}
