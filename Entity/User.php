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

use Austral\EntityBundle\Entity\Interfaces\FileInterface;
use Austral\EntityFileBundle\Entity\Traits\EntityFileCropperTrait;
use Austral\EntityFileBundle\Entity\Traits\EntityFileTrait;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\EntityFileBundle\Annotation as AustralFile;

use Austral\EntityBundle\Entity\Entity;
use Austral\EntityBundle\Entity\EntityInterface;
use Austral\EntityBundle\Entity\Traits as Traits;


use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Austral User Entity.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 * @ORM\MappedSuperclass
 */
abstract class User extends Entity implements UserInterface, EntityInterface, FileInterface, PasswordAuthenticatedUserInterface
{

  const USER_TYPE_ROOT = "root";
  const USER_TYPE_ADMIN = "admin";
  const USER_TYPE_USER = "user";

  const USER_INTERFACE_AUTO = "auto";
  const USER_INTERFACE_DARK = "dark";
  const USER_INTERFACE_LIGHT = "light";

  use Traits\EntityTimestampableTrait;
  use EntityFileTrait;
  use EntityFileCropperTrait;

  /**
   * @var string
   * @ORM\Column(name="id", type="string", length=40)
   * @ORM\Id
   */
  protected $id;

  /**
   * @ORM\ManyToMany(targetEntity="\Austral\SecurityBundle\Entity\Interfaces\RoleInterface", inversedBy="users", cascade={"persist", "remove"})
   * @ORM\JoinTable(name="austral_security_user_role",
   *   joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
   * )
   */
  protected Collection $securityRoles;

  /**
   * @ORM\ManyToMany(targetEntity="\Austral\SecurityBundle\Entity\Interfaces\GroupInterface", inversedBy="users", cascade={"persist", "remove"})
   * @ORM\JoinTable(name="austral_security_user_group",
   *   joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
   * )
   */
  protected Collection $groups;

  /**
   * @var string|null
   * @ORM\Column(type="string", length=128, unique=true)
   */
  protected ?string $username = null;

  /**
   * @var string|null
   * @ORM\Column(type="string", length=128, unique=true)
   */
  protected ?string $email = null;

  /**
   * @var string|null
   * @ORM\Column(name="firstname", type="string", length=64, nullable=true)
   */
  protected ?string $firstname = null;

  /**
   * @var string|null
   * @ORM\Column(name="lastname", type="string", length=64, nullable=true)
   */
  protected ?string $lastname = null;

  /**
   * @var string|null
   * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
   * @AustralFile\UploadParameters(configName="avatar")
   * @AustralFile\ImageSize()
   * @AustralFile\Croppers({
   *  "avatar",
   * })
   */
  protected ?string $avatar = null;

  /**
   * @var string|null
   * @ORM\Column(name="avatar_color", type="string", length=255, nullable=true )
   */
  protected ?string $avatarColor = null;

  /**
   * @var string|null
   * @ORM\Column(name="language", type="string", length=255, nullable=true )
   */
  protected ?string $language = null;

  /**
   * @var string|null
   * @ORM\Column(name="password", type="string", length=255)
   */
  protected ?string $password = null;

  /**
   * @var string|null
   * @ORM\Column(name="salt", type="string", length=255)
   */
  protected ?string $salt = null;

  /**
   * @var string|null
   */
  protected ?string $plainPassword = null;

  /**
   * @var boolean
   * @ORM\Column(name="is_active", type="boolean")
   */
  protected bool $isActive = false;

  /**
   * @var string|null
   * @ORM\Column(name="forgot_password_token", type="text", nullable=true )
   */
  protected ?string $forgotPasswordToken = null;
  
  /**
   * @var string|null
   * @ORM\Column(name="type_user", type="string", length=255, nullable=true )
   */
  protected ?string $typeUser = null;

  /**
   * @var string|null
   * @ORM\Column(name="interface_theme", type="string", length=255, nullable=true )
   */
  protected ?string $interfaceTheme = null;

  /**
   * @return string
   */
  public function __toString()
  {
    return "{$this->lastname} {$this->firstname}";
  }

  /**
   * @return array
   */
  public function getFieldsToDelete(): array
  {
    return array("avatar");
  }

  /**
   * Role constructor.
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();
    $this->securityRoles = new ArrayCollection();
    $this->groups = new ArrayCollection();
    $this->id = Uuid::uuid4()->toString();
    $this->isActive  = false;
    $this->typeUser = self::USER_TYPE_USER;
  }

  /**
   * @return array
   */
  public function arrayObject(): array
  {
    return array(
      "id"              =>  $this->getId(),
      "firstname"       =>  $this->getFirstname(),
      "lastname"        =>  $this->getLastname(),
      "created"         =>  $this->getCreated()->format("Y-m-d"),
    );
  }

  /**
   * @return bool
   */
  public function isSuperAdmin(): bool
  {
    return $this->typeUser === self::USER_TYPE_ROOT;
  }

  /**
   * @return bool
   */
  public function isAdmin(): bool
  {
    return $this->typeUser === self::USER_TYPE_ADMIN;
  }

  /**
   * @return bool
   */
  public function isUser(): bool
  {
    return $this->typeUser === self::USER_TYPE_USER;
  }

  public function eraseCredentials()
  {
    // TODO: Implement eraseCredentials() method.
  }

  /**
   * Add role
   *
   * @param RoleInterface $role
   * @return $this
   */
  public function addSecurityRole(RoleInterface $role): User
  {
    if(!$this->securityRoles->contains($role))
    {
      $this->securityRoles[] = $role;
      $role->addUser($this);
    }
    return $this;
  }

  /**
   * Remove securityRole
   *
   * @param RoleInterface $role
   *
   * @return $this
   */
  public function removeSecurityRole(RoleInterface $role): User
  {
    if($this->securityRoles->contains($role))
    {
      $this->securityRoles->removeElement($role);
      $role->removeUser($this);
    }
    return $this;
  }

  /**
   * Get securityRoles
   * @return Collection
   */
  public function getSecurityRoles(): Collection
  {
    return $this->securityRoles;
  }

  /**
   * @return array
   */
  public function getRoles(): array
  {
    $roles = array();
    if($this->isSuperAdmin())
    {
      $roles[] = "ROLE_ROOT";
    }
    if($this->isAdmin())
    {
      $roles[] = "ROLE_ADMIN_ACCESS";
    }
    if($this->isUser())
    {
      $roles[] = "ROLE_USER_ACCESS";
    }
    /** @var RoleInterface $securityRole */
    foreach($this->getSecurityRoles() as $securityRole)
    {
      $roles[] = $securityRole->getRole();
    }
    /** @var GroupInterface $group */
    foreach ($this->getGroups() as $group)
    {
      /** @var ArrayCollection $groupRoles */
      if($groupRoles = $group->getRoles())
      {
        /** @var RoleInterface $role */
        foreach($groupRoles as $role)
        {
          if(!in_array($role->getRole(), $roles))
          {
            $roles[] = $role->getRole();
          }
        }
      }
    }
    return $roles;
  }

  /**
   * Add group
   *
   * @param GroupInterface $group
   * @return $this
   */
  public function addGroup(GroupInterface $group): User
  {
    if(!$this->groups->contains($group))
    {
      $this->groups[] = $group;
      $group->addUser($this);
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
  public function removeGroup(GroupInterface $group): User
  {
    if($this->groups->contains($group))
    {
      $this->groups->removeElement($group);
      $group->removeUser($this);
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
   * @return string|null
   */
  public function getUserIdentifier(): ?string
  {
    return $this->username;
  }

  /**
   * Get username
   * @return string|null
   */
  public function getUsername(): ?string
  {
    return $this->username;
  }

  /**
   * Set username
   *
   * @param string $username
   *
   * @return $this
   */
  public function setUsername(string $username): User
  {
    $this->username = $username;
    return $this;
  }

  /**
   * Get email
   * @return string|null
   */
  public function getEmail(): ?string
  {
    return $this->email;
  }

  /**
   * Set email
   *
   * @param string $email
   *
   * @return $this
   */
  public function setEmail(string $email): User
  {
    $this->email = $email;
    return $this;
  }

  /**
   * Get firstname
   * @return string|null
   */
  public function getFirstname(): ?string
  {
    return $this->firstname;
  }

  /**
   * Set firstname
   *
   * @param string|null $firstname
   *
   * @return $this
   */
  public function setFirstname(?string $firstname): User
  {
    $this->firstname = $firstname;
    return $this;
  }

  /**
   * Get lastname
   * @return string
   */
  public function getLastname(): ?string
  {
    return $this->lastname;
  }

  /**
   * Set lastname
   *
   * @param string|null $lastname
   *
   * @return $this
   */
  public function setLastname(?string $lastname): User
  {
    $this->lastname = $lastname;
    return $this;
  }

  /**
   * Get avatar
   * @return string
   */
  public function getAvatar(): ?string
  {
    return $this->avatar;
  }

  /**
   * Set avatar
   *
   * @param string|null $avatar
   *
   * @return $this
   */
  public function setAvatar(?string $avatar): User
  {
    $this->avatar = $avatar;
    return $this;
  }

  /**
   * Get avatarColor
   * @return string
   */
  public function getAvatarColor(): ?string
  {
    return $this->avatarColor;
  }

  /**
   * Set avatarColor
   *
   * @param string|null $avatarColor
   *
   * @return $this
   */
  public function setAvatarColor(?string $avatarColor): User
  {
    $this->avatarColor = $avatarColor;
    return $this;
  }

  /**
   * Get language
   * @return string|null
   */
  public function getLanguage(): ?string
  {
    return $this->language;
  }

  /**
   * Set language
   *
   * @param string|null $language
   *
   * @return $this
   */
  public function setLanguage(?string $language): User
  {
    $this->language = $language;
    return $this;
  }

  /**
   * Get password
   * @return string|null
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  /**
   * Set password
   *
   * @param string $password
   *
   * @return $this
   */
  public function setPassword(string $password): User
  {
    $this->password = $password;
    return $this;
  }

  /**
   * Get salt
   * @return string|null
   */
  public function getSalt(): ?string
  {
    return $this->salt;
  }

  /**
   * Set salt
   *
   * @param string $salt
   *
   * @return $this
   */
  public function setSalt(string $salt): User
  {
    $this->salt = $salt;
    return $this;
  }

  /**
   * Get plainPassword
   * @return string
   */
  public function getPlainPassword(): ?string
  {
    return $this->plainPassword;
  }

  /**
   * Set plainPassword
   *
   * @param string|null $plainPassword
   *
   * @return $this
   */
  public function setPlainPassword(?string $plainPassword): User
  {
    $this->plainPassword = $plainPassword;
    return $this;
  }

  /**
   * Get isActive
   * @return bool
   */
  public function getIsActive(): bool
  {
    return $this->isActive;
  }

  /**
   * Set isActive
   *
   * @param bool $isActive
   *
   * @return $this
   */
  public function setIsActive(bool $isActive): User
  {
    $this->isActive = $isActive;
    return $this;
  }

  /**
   * Get forgotPasswordToken
   * @return string|null
   */
  public function getForgotPasswordToken(): ?string
  {
    return $this->forgotPasswordToken;
  }

  /**
   * Set forgotPasswordToken
   *
   * @param string|null $forgotPasswordToken
   *
   * @return $this
   */
  public function setForgotPasswordToken(?string $forgotPasswordToken): User
  {
    $this->forgotPasswordToken = $forgotPasswordToken;
    return $this;
  }

  /**
   * Get typeUser
   * @return string|null
   */
  public function getTypeUser(): ?string
  {
    return $this->typeUser;
  }

  /**
   * Set typeUser
   *
   * @param string|null $typeUser
   *
   * @return $this
   */
  public function setTypeUser(?string $typeUser): User
  {
    $this->typeUser = $typeUser;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getInterfaceTheme(): ?string
  {
    return $this->interfaceTheme ?? self::USER_INTERFACE_AUTO;
  }

  /**
   * @param string|null $interfaceTheme
   *
   * @return User
   */
  public function setInterfaceTheme(?string $interfaceTheme): User
  {
    $this->interfaceTheme = $interfaceTheme;
    return $this;
  }

}
