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
 * Austral Group Entity.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 * @ORM\MappedSuperclass
 */
abstract class Group extends Entity implements GroupInterface, EntityInterface
{
  use Traits\EntityTimestampableTrait;

  /**
   * @var string
   * @ORM\Column(name="id", type="string", length=40)
   * @ORM\Id
   */
  protected $id;

  /**
   * @ORM\ManyToMany(targetEntity="\Austral\SecurityBundle\Entity\Interfaces\UserInterface", mappedBy="groups", cascade={"persist", "remove"})
   */
  protected Collection $users;

  /**
   * @ORM\ManyToMany(targetEntity="\Austral\SecurityBundle\Entity\Interfaces\RoleInterface", inversedBy="groups", cascade={"persist", "remove"})
   * @ORM\JoinTable(name="austral_security_role_group",
   *   joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
   * )
   */
  protected Collection $roles;

  /**
   * @var string|null
   * @ORM\Column(name="name", type="string", length=255, nullable=false)
   */
  protected ?string $name = null;

  /**
   * @var string|null
   * @ORM\Column(name="keyname", type="string", length=255, nullable=false)
   */
  protected ?string $keyname = null;

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Group constructor.
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();
    $this->users = new ArrayCollection();
    $this->roles = new ArrayCollection();
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
      "created"         =>  $this->getCreated()->format("Y-m-d"),
    );
  }

  /**
   * Add user
   *
   * @param UserInterface $user
   * @return $this
   */
  public function addUser(UserInterface $user): Group
  {
    if(!$this->users->contains($user))
    {
      $this->users[] = $user;
      $user->addGroup($this);
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
  public function removeUser(UserInterface $user): Group
  {
    if($this->users->contains($user))
    {
      $this->users->removeElement($user);
      $user->removeGroup($this);
    }
    return $this;
  }

  /**
   * Get users
   * @return Collection
   */
  public function getUsers(): Collection
  {
    return $this->users;
  }

  /**
   * Add role
   *
   * @param RoleInterface $role
   * @return $this
   */
  public function addRole(RoleInterface $role): Group
  {
    if(!$this->roles->contains($role))
    {
      $this->roles[] = $role;
    }
    return $this;
  }

  /**
   * Remove role
   *
   * @param RoleInterface $role
   *
   * @return $this
   */
  public function removeRole(RoleInterface $role): Group
  {
    if($this->roles->contains($role))
    {
      $this->roles->removeElement($role);
    }
    return $this;
  }

  /**
   * Get role
   * @return Collection
   */
  public function getRoles(): Collection
  {
    return $this->roles;
  }

  /**
   * getRolesArray
   *
   * @return array
   */
  public function getRolesArray(): array
  {
    $groupRoles = array();
    /** @var RoleInterface $role */
    foreach($this->getRoles() as $role)
    {
      if(!in_array($role->getRole(), $groupRoles))
      {
        $groupRoles[] = $role->getRole();
      }
    }
    return $groupRoles;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return $this
   */
  public function setName(string $name): Group
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
   * Get keyname
   * @return string|null
   */
  public function getKeyname(): ?string
  {
    return $this->keyname;
  }

  /**
   * @param string|null $keyname
   *
   * @return $this
   */
  public function setKeyname(string $keyname = null): Group
  {
    $this->keyname = $keyname ? $this->keynameGenerator($keyname) : $this->keynameGenerator($this->name);
    return $this;
  }

}
