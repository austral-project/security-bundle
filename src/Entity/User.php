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

use Austral\Contracts\File\CropperInterface;
use Austral\EntityBundle\Entity\Entity;
use Austral\EntityBundle\Entity\Traits\EntityTimestampableTrait;
use Austral\EntityFileBundle\Attribute as AustralFile;
use Austral\EntityFileBundle\Entity\Traits\EntityFileCropperTrait;
use Austral\EntityFileBundle\Entity\Traits\EntityFileTrait;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Austral User.
 *
 * Mapped superclass of the user of the application (App\Entity\Austral\SecurityBundle\User).
 *
 * In the session, the user keeps only its id, its identifiers, its type, its status and a fingerprint of
 * its password: isEqualTo() logs the user out when its password changes or when it is disabled.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends Entity<object>
 *
 * @implements CropperInterface<object>
 */
#[ORM\MappedSuperclass]
abstract class User extends Entity implements UserInterface, CropperInterface
{
  /** @use EntityFileTrait<object> */
  use EntityFileTrait;

  /** @use EntityFileCropperTrait<object> */
  use EntityFileCropperTrait;

  use EntityTimestampableTrait;

  #[ORM\Column(name: 'id', type: Types::STRING, length: 40)]
  #[ORM\Id]
  protected string $id;

  /**
   * @var Collection<int, RoleInterface>
   */
  #[ORM\ManyToMany(targetEntity: RoleInterface::class, inversedBy: 'users', cascade: ['persist'])]
  #[ORM\JoinTable(name: 'austral_security_user_role')]
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  protected Collection $securityRoles;

  /**
   * @var Collection<int, GroupInterface>
   */
  #[ORM\ManyToMany(targetEntity: GroupInterface::class, inversedBy: 'users', cascade: ['persist'])]
  #[ORM\JoinTable(name: 'austral_security_user_group')]
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  protected Collection $groups;

  #[ORM\Column(name: 'username', type: Types::STRING, length: 255, unique: true)]
  protected string $username = '';

  #[ORM\Column(name: 'email', type: Types::STRING, length: 255, unique: true)]
  protected string $email = '';

  #[ORM\Column(name: 'firstname', type: Types::STRING, length: 255, nullable: true)]
  protected ?string $firstname = null;

  #[ORM\Column(name: 'lastname', type: Types::STRING, length: 255, nullable: true)]
  protected ?string $lastname = null;

  #[ORM\Column(name: 'avatar', type: Types::STRING, length: 255, nullable: true)]
  #[AustralFile\UploadParameters(configName: 'avatar')]
  #[AustralFile\ImageSize]
  #[AustralFile\Croppers(croppers: [new AustralFile\Cropper(name: 'avatar')])]
  protected ?string $avatar = null;

  #[ORM\Column(name: 'avatar_color', type: Types::STRING, length: 34, nullable: true)]
  protected ?string $avatarColor = null;

  #[ORM\Column(name: 'language', type: Types::STRING, length: 4, nullable: true)]
  protected ?string $language = null;

  #[ORM\Column(name: 'password', type: Types::STRING, length: 255)]
  protected string $password = '';

  protected ?string $plainPassword = null;

  #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
  protected bool $isActive = false;

  #[ORM\Column(name: 'type_user', type: Types::STRING, length: 255, nullable: true)]
  protected ?string $typeUser = null;

  #[ORM\Column(name: 'interface_theme', type: Types::STRING, length: 255, nullable: true)]
  protected ?string $interfaceTheme = null;

  #[ORM\Column(name: 'password_reset_selector', type: Types::STRING, length: 32, unique: true, nullable: true)]
  protected ?string $passwordResetSelector = null;

  #[ORM\Column(name: 'password_reset_hash', type: Types::STRING, length: 128, nullable: true)]
  protected ?string $passwordResetHash = null;

  #[ORM\Column(name: 'password_reset_expires_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
  protected ?\DateTimeImmutable $passwordResetExpiresAt = null;

  /**
   * Fingerprint of the password of a user read from the session (the hash itself is not stored there).
   */
  private ?string $passwordFingerprint = null;

  public function __construct()
  {
    parent::__construct();
    $this->id = Uuid::v4()->toRfc4122();
    $this->securityRoles = new ArrayCollection();
    $this->groups = new ArrayCollection();
    $this->typeUser = self::USER_TYPE_USER;
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    $name = trim("{$this->lastname} {$this->firstname}");

    return '' !== $name ? $name : $this->email;
  }

  /**
   * What the session keeps of the user: never the password hash nor the reset token.
   *
   * @return array<string, mixed>
   */
  public function __serialize(): array
  {
    return [
      'id' => $this->id,
      'username' => $this->username,
      'email' => $this->email,
      'typeUser' => $this->typeUser,
      'isActive' => $this->isActive,
      'passwordFingerprint' => self::fingerprint($this->password),
    ];
  }

  /**
   * @param array<string, mixed> $data
   *
   * @return void
   */
  public function __unserialize(array $data): void
  {
    $this->id = (string) ($data['id'] ?? '');
    $this->username = (string) ($data['username'] ?? '');
    $this->email = (string) ($data['email'] ?? '');
    $this->typeUser = isset($data['typeUser']) ? (string) $data['typeUser'] : null;
    $this->isActive = (bool) ($data['isActive'] ?? false);
    $this->passwordFingerprint = isset($data['passwordFingerprint']) ? (string) $data['passwordFingerprint'] : null;
    $this->securityRoles = new ArrayCollection();
    $this->groups = new ArrayCollection();
  }

  /**
   * Same user, same identifier, same password and still active: otherwise the user is logged out.
   *
   * @param SymfonyUserInterface $user The user reloaded from the database
   *
   * @return bool
   */
  public function isEqualTo(SymfonyUserInterface $user): bool
  {
    if (!$user instanceof UserInterface)
    {
      return false;
    }

    return $user->getId() === $this->id
      && $user->getUserIdentifier() === $this->getUserIdentifier()
      && $user->getIsActive()
      && hash_equals($this->passwordFingerprint ?? self::fingerprint($this->password), self::fingerprint($user->getPassword()));
  }

  /**
   * @param string|null $password
   *
   * @return string
   */
  private static function fingerprint(?string $password): string
  {
    return hash('sha256', (string) $password);
  }

  /**
   * @return array<string, mixed>
   */
  public function arrayObject(): array
  {
    return [
      'id' => $this->getId(),
      'firstname' => $this->getFirstname(),
      'lastname' => $this->getLastname(),
      'created' => $this->getCreated()->format('Y-m-d'),
    ];
  }

  public function isSuperAdmin(): bool
  {
    return self::USER_TYPE_ROOT === $this->getTypeUser();
  }

  public function isAdmin(): bool
  {
    return self::USER_TYPE_ADMIN === $this->getTypeUser();
  }

  public function isUser(): bool
  {
    return self::USER_TYPE_USER === $this->getTypeUser();
  }

  /**
   * Role of the type of user, roles of the user and roles of its groups.
   *
   * @return list<string>
   */
  public function getRoles(): array
  {
    $roles = [match ($this->getTypeUser())
    {
      self::USER_TYPE_ROOT => self::ROLE_ROOT,
      self::USER_TYPE_ADMIN => self::ROLE_ADMIN_ACCESS,
      default => self::ROLE_USER_ACCESS,
    }];
    foreach ($this->securityRoles as $securityRole)
    {
      $roles[] = $securityRole->getRole();
    }
    foreach ($this->groups as $group)
    {
      foreach ($group->getRoles() as $role)
      {
        $roles[] = $role->getRole();
      }
    }

    return array_values(array_unique($roles));
  }

  public function addSecurityRole(RoleInterface $role): self
  {
    if (!$this->securityRoles->contains($role))
    {
      $this->securityRoles->add($role);
      $role->addUser($this);
    }

    return $this;
  }

  public function removeSecurityRole(RoleInterface $role): self
  {
    if ($this->securityRoles->removeElement($role))
    {
      $role->removeUser($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, RoleInterface>
   */
  public function getSecurityRoles(): Collection
  {
    return $this->securityRoles;
  }

  public function addGroup(GroupInterface $group): self
  {
    if (!$this->groups->contains($group))
    {
      $this->groups->add($group);
      $group->addUser($this);
    }

    return $this;
  }

  public function removeGroup(GroupInterface $group): self
  {
    if ($this->groups->removeElement($group))
    {
      $group->removeUser($this);
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

  /**
   * @return non-empty-string The username, the id when the username is empty
   */
  public function getUserIdentifier(): string
  {
    $identifier = '' !== $this->username ? $this->username : $this->id;
    if ('' === $identifier)
    {
      throw new \LogicException('A user has no identifier: its username and its id are empty.');
    }

    return $identifier;
  }

  /**
   * The plain password is cleared by the PasswordUpdater once hashed.
   */
  #[\Deprecated]
  public function eraseCredentials(): void
  {
  }

  public function getUsername(): string
  {
    return $this->username;
  }

  public function setUsername(string $username): self
  {
    $this->username = $username;

    return $this;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getFirstname(): ?string
  {
    return $this->firstname;
  }

  public function setFirstname(?string $firstname): self
  {
    $this->firstname = $firstname;

    return $this;
  }

  public function getLastname(): ?string
  {
    return $this->lastname;
  }

  public function setLastname(?string $lastname): self
  {
    $this->lastname = $lastname;

    return $this;
  }

  public function getAvatar(): ?string
  {
    return $this->avatar;
  }

  public function setAvatar(?string $avatar): self
  {
    $this->avatar = $avatar;

    return $this;
  }

  public function getAvatarColor(): ?string
  {
    return $this->avatarColor;
  }

  public function setAvatarColor(?string $avatarColor): self
  {
    $this->avatarColor = $avatarColor;

    return $this;
  }

  public function getLanguage(): ?string
  {
    return $this->language;
  }

  public function setLanguage(?string $language): self
  {
    $this->language = $language;

    return $this;
  }

  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  public function getPlainPassword(): ?string
  {
    return $this->plainPassword;
  }

  public function setPlainPassword(?string $plainPassword): self
  {
    $this->plainPassword = $plainPassword;

    return $this;
  }

  public function getIsActive(): bool
  {
    return $this->isActive;
  }

  public function setIsActive(bool $isActive): self
  {
    $this->isActive = $isActive;

    return $this;
  }

  public function getTypeUser(): string
  {
    return $this->typeUser ?? self::USER_TYPE_USER;
  }

  public function setTypeUser(string $typeUser): self
  {
    if (!\in_array($typeUser, [self::USER_TYPE_ROOT, self::USER_TYPE_ADMIN, self::USER_TYPE_USER], true))
    {
      throw new \InvalidArgumentException(\sprintf('Unknown type of user "%s".', $typeUser));
    }
    $this->typeUser = $typeUser;

    return $this;
  }

  public function getInterfaceTheme(): string
  {
    return $this->interfaceTheme ?? self::USER_INTERFACE_AUTO;
  }

  public function setInterfaceTheme(?string $interfaceTheme): self
  {
    $this->interfaceTheme = $interfaceTheme;

    return $this;
  }

  public function getPasswordResetSelector(): ?string
  {
    return $this->passwordResetSelector;
  }

  public function getPasswordResetHash(): ?string
  {
    return $this->passwordResetHash;
  }

  public function getPasswordResetExpiresAt(): ?\DateTimeImmutable
  {
    return $this->passwordResetExpiresAt;
  }

  public function setPasswordReset(string $selector, string $hash, \DateTimeImmutable $expiresAt): self
  {
    $this->passwordResetSelector = $selector;
    $this->passwordResetHash = $hash;
    $this->passwordResetExpiresAt = $expiresAt;

    return $this;
  }

  public function clearPasswordReset(): self
  {
    $this->passwordResetSelector = null;
    $this->passwordResetHash = null;
    $this->passwordResetExpiresAt = null;

    return $this;
  }
}
