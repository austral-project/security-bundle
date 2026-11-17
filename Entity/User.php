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

use Austral\EntityFileBundle\Entity\Interfaces\CropperInterface;
use Austral\EntityFileBundle\Entity\Traits\EntityFileCropperTrait;
use Austral\EntityFileBundle\Entity\Traits\EntityFileTrait;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\EntityFileBundle\Attribute as AustralFile;

use Austral\EntityBundle\Entity\Entity;
use Austral\EntityBundle\Entity\Traits as Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Austral User Entity.
 * @template T of object
 *
 * @extends Entity<T>
 * @implements UserInterface<T>
 * @implements CropperInterface<T>
 * @use EntityFileTrait<T>
 * @use EntityFileCropperTrait<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
#[ORM\MappedSuperclass]
abstract class User extends Entity implements UserInterface, CropperInterface
{

  /** @use EntityFileTrait<T> */
  use EntityFileTrait;

  /** @use EntityFileCropperTrait<T> */
  use EntityFileCropperTrait;

  use Traits\EntityTimestampableTrait;

  /**
   * @var string
   */
  #[Orm\Column(name: 'id', type: 'string', length: 40)]
  #[ORM\Id()]
  protected string $id;

  /**
   * @var Collection<int|string, RoleInterface<T>>
   */
  #[Orm\ManyToMany(targetEntity: RoleInterface::class, inversedBy: "users", cascade: ["persist"])]
  #[Orm\JoinTable(name: "austral_security_user_role",
    joinColumns: [
      new Orm\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")
    ],
    inverseJoinColumns: [
      new Orm\JoinColumn(name: "role_id", referencedColumnName: "id", onDelete: "CASCADE")
    ]
  )]
  protected Collection $securityRoles;

  /**
   * @var Collection<int|string, GroupInterface<T>>
   */
  #[Orm\ManyToMany(targetEntity: GroupInterface::class, inversedBy: "users", cascade: ["persist"])]
  #[Orm\JoinTable(name: "austral_security_user_group",
    joinColumns: [
      new Orm\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")
    ],
    inverseJoinColumns: [
      new Orm\JoinColumn(name: "role_id", referencedColumnName: "id", onDelete: "CASCADE")
    ]
  )]
  protected Collection $groups;

  /**
   * @var string
   */
  #[ORM\Column(name: 'username', type: 'string', length: 255, unique: true, nullable: false)]
  protected string $username = "";

  /**
   * @var string
   */
  #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true, nullable: false)]
  protected string $email = "";

  /**
   * @var string|null
   */
  #[ORM\Column(name: 'firstname', type: 'string', length: 255, nullable: true)]
  protected ?string $firstname = null;

  /**
   * @var string|null
   */
  #[ORM\Column(name: 'lastname', type: 'string', length: 255, nullable: true)]
  protected ?string $lastname = null;

  /**
   * @var string|null
   */
  #[ORM\Column(name: 'avatar', type: 'string', length: 255, nullable: true)]
  #[AustralFile\UploadParameters(configName: "avatar")]
  #[AustralFile\ImageSize()]
  #[AustralFile\Croppers(croppers: [
    new AustralFile\Cropper(name: "avatar")
  ])]
  protected ?string $avatar = null;

  /**
   * @var string|null
   */
  #[ORM\Column(name: 'avatar_color', type: 'string', length: 34, nullable: true)]
  protected ?string $avatarColor = null;

  /**
   * @var string|null
   */
  #[ORM\Column(name: 'language', type: 'string', length: 4, nullable: true)]
  protected ?string $language = null;

  /**
   * @var string
   */
  #[ORM\Column(name: 'password', type: 'string', length: 255)]
  protected string $password = "";

  /**
   * @var string
   */
  #[ORM\Column(name: 'salt', type: 'string', length: 255)]
  protected string $salt = "";

  /**
   * @var string|null
   */
  protected ?string $plainPassword = null;

  /**
   * @var boolean
   * @ORM\Column(name="is_active", type="boolean")
   */
  #[ORM\Column(name: 'is_active', type: 'boolean')]
  protected bool $isActive = false;

  /**
   * @var string|null
   */
  #[ORM\Column(name: 'forgot_password_token', type: 'text', nullable: true)]
  protected ?string $forgotPasswordToken = null;
  
  /**
   * @var string|null
   */
  #[ORM\Column(name: 'type_user', type: 'string', length: 255, nullable: true)]
  protected ?string $typeUser = null;

  /**
   * @var string|null
   * @ORM\Column(name="interface_theme", type="string", length=255, nullable=true )
   */
  #[ORM\Column(name: 'interface_theme', type: 'string', length: 255, nullable: true)]
  protected ?string $interfaceTheme = null;

  /**
   * @return string
   */
  public function __toString()
  {
    return "{$this->lastname} {$this->firstname}";
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
   * @return array<string, mixed>
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

  /**
   * Required by Symfony\Component\Security\Core\User\UserInterface until Symfony 8.
   * Nothing to erase: the plain password is never serialized.
   *
   * @return void
   */
  #[\Deprecated]
  public function eraseCredentials(): void
  {
  }

  /**
   * @inheritDoc
   */
  public function addSecurityRole(RoleInterface $role): self
  {
    if(!$this->securityRoles->contains($role))
    {
      $this->securityRoles[] = $role;
      $role->addUser($this);
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function removeSecurityRole(RoleInterface $role): self
  {
    if($this->securityRoles->contains($role))
    {
      $this->securityRoles->removeElement($role);
      $role->removeUser($this);
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getSecurityRoles(): Collection
  {
    return $this->securityRoles;
  }

  /**
   * @inheritDoc
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
    /** @var RoleInterface<T> $securityRole */
    foreach($this->getSecurityRoles() as $securityRole)
    {
      $roles[] = $securityRole->getRole();
    }
    /** @var GroupInterface<T> $group */
    foreach ($this->getGroups() as $group)
    {
      /** @var RoleInterface<T> $role */
      foreach($group->getRoles() as $role)
      {
        if(!in_array($role->getRole(), $roles))
        {
          $roles[] = $role->getRole();
        }
      }
    }
    return $roles;
  }

  /**
   * @inheritDoc
   */
  public function addGroup(GroupInterface $group): self
  {
    if(!$this->groups->contains($group))
    {
      $this->groups[] = $group;
      $group->addUser($this);
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function removeGroup(GroupInterface $group): self
  {
    if($this->groups->contains($group))
    {
      $this->groups->removeElement($group);
      $group->removeUser($this);
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getGroups(): Collection
  {
    return $this->groups;
  }

  /**
   * @inheritDoc
   */
  public function getUserIdentifier(): string
  {
    /** @var non-empty-string $userIdentifier */
    $userIdentifier = $this->getUsername() !== "" ? $this->getUsername() : $this->getId();
    return $userIdentifier;
  }

  /**
   * @inheritDoc
   */
  public function getUsername(): string
  {
    return $this->username;
  }

  /**
   * @inheritDoc
   */
  public function setUsername(string $username): self
  {
    $this->username = $username;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getEmail(): string
  {
    return $this->email;
  }

  /**
   * @inheritDoc
   */
  public function setEmail(string $email): self
  {
    $this->email = $email;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFirstname(): ?string
  {
    return $this->firstname;
  }

  /**
   * @inheritDoc
   */
  public function setFirstname(?string $firstname): self
  {
    $this->firstname = $firstname;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getLastname(): ?string
  {
    return $this->lastname;
  }

  /**
   * @inheritDoc
   */
  public function setLastname(?string $lastname): self
  {
    $this->lastname = $lastname;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getAvatar(): ?string
  {
    return $this->avatar;
  }

  /**
   * @inheritDoc
   */
  public function setAvatar(?string $avatar): self
  {
    $this->avatar = $avatar;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getAvatarColor(): ?string
  {
    return $this->avatarColor;
  }

  /**
   * @inheritDoc
   */
  public function setAvatarColor(?string $avatarColor): self
  {
    $this->avatarColor = $avatarColor;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getLanguage(): ?string
  {
    return $this->language;
  }

  /**
   * @inheritDoc
   */
  public function setLanguage(?string $language): self
  {
    $this->language = $language;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  /**
   * @inheritDoc
   */
  public function setPassword(string $password): self
  {
    $this->password = $password;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getSalt(): ?string
  {
    return $this->salt;
  }

  /**
   * @inheritDoc
   */
  public function setSalt(string $salt): self
  {
    $this->salt = $salt;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getPlainPassword(): ?string
  {
    return $this->plainPassword;
  }

  /**
   * @inheritDoc
   */
  public function setPlainPassword(?string $plainPassword): self
  {
    $this->plainPassword = $plainPassword;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getIsActive(): bool
  {
    return $this->isActive;
  }

  /**
   * @inheritDoc
   */
  public function setIsActive(bool $isActive): self
  {
    $this->isActive = $isActive;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getForgotPasswordToken(): ?string
  {
    return $this->forgotPasswordToken;
  }

  /**
   * @inheritDoc
   */
  public function setForgotPasswordToken(?string $forgotPasswordToken): self
  {
    $this->forgotPasswordToken = $forgotPasswordToken;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getTypeUser(): ?string
  {
    return $this->typeUser;
  }

  /**
   * @inheritDoc
   */
  public function setTypeUser(?string $typeUser): self
  {
    $this->typeUser = $typeUser;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getInterfaceTheme(): ?string
  {
    return $this->interfaceTheme ?? self::USER_INTERFACE_AUTO;
  }

  /**
   * @inheritDoc
   */
  public function setInterfaceTheme(?string $interfaceTheme): self
  {
    $this->interfaceTheme = $interfaceTheme;
    return $this;
  }

}
