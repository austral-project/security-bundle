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

use Austral\Contracts\File\FileInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * Austral User Interface.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends FileInterface<object>
 */
interface UserInterface extends SymfonyUserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, FileInterface
{
  public const string USER_TYPE_ROOT = 'root';
  public const string USER_TYPE_ADMIN = 'admin';
  public const string USER_TYPE_USER = 'user';

  public const string USER_INTERFACE_AUTO = 'auto';
  public const string USER_INTERFACE_DARK = 'dark';
  public const string USER_INTERFACE_LIGHT = 'light';

  public const string ROLE_ROOT = 'ROLE_ROOT';
  public const string ROLE_ADMIN_ACCESS = 'ROLE_ADMIN_ACCESS';
  public const string ROLE_USER_ACCESS = 'ROLE_USER_ACCESS';

  /**
   * isSuperAdmin
   *
   * @return bool
   */
  public function isSuperAdmin(): bool;

  /**
   * isAdmin
   *
   * @return bool
   */
  public function isAdmin(): bool;

  /**
   * isUser
   *
   * @return bool
   */
  public function isUser(): bool;

  /**
   * addSecurityRole
   *
   * @param RoleInterface $role
   *
   * @return self
   */
  public function addSecurityRole(RoleInterface $role): self;

  /**
   * removeSecurityRole
   *
   * @param RoleInterface $role
   *
   * @return self
   */
  public function removeSecurityRole(RoleInterface $role): self;

  /**
   * @return Collection<int, RoleInterface>
   */
  public function getSecurityRoles(): Collection;

  /**
   * addGroup
   *
   * @param GroupInterface $group
   *
   * @return self
   */
  public function addGroup(GroupInterface $group): self;

  /**
   * removeGroup
   *
   * @param GroupInterface $group
   *
   * @return self
   */
  public function removeGroup(GroupInterface $group): self;

  /**
   * @return Collection<int, GroupInterface>
   */
  public function getGroups(): Collection;

  /**
   * getUsername
   *
   * @return string
   */
  public function getUsername(): string;

  /**
   * setUsername
   *
   * @param string $username
   *
   * @return self
   */
  public function setUsername(string $username): self;

  /**
   * getEmail
   *
   * @return string
   */
  public function getEmail(): string;

  /**
   * setEmail
   *
   * @param string $email
   *
   * @return self
   */
  public function setEmail(string $email): self;

  /**
   * getFirstname
   *
   * @return string|null
   */
  public function getFirstname(): ?string;

  /**
   * setFirstname
   *
   * @param string|null $firstname
   *
   * @return self
   */
  public function setFirstname(?string $firstname): self;

  /**
   * getLastname
   *
   * @return string|null
   */
  public function getLastname(): ?string;

  /**
   * setLastname
   *
   * @param string|null $lastname
   *
   * @return self
   */
  public function setLastname(?string $lastname): self;

  /**
   * getAvatar
   *
   * @return string|null
   */
  public function getAvatar(): ?string;

  /**
   * setAvatar
   *
   * @param string|null $avatar
   *
   * @return self
   */
  public function setAvatar(?string $avatar): self;

  /**
   * getAvatarColor
   *
   * @return string|null
   */
  public function getAvatarColor(): ?string;

  /**
   * setAvatarColor
   *
   * @param string|null $avatarColor
   *
   * @return self
   */
  public function setAvatarColor(?string $avatarColor): self;

  /**
   * getLanguage
   *
   * @return string|null
   */
  public function getLanguage(): ?string;

  /**
   * setLanguage
   *
   * @param string|null $language
   *
   * @return self
   */
  public function setLanguage(?string $language): self;

  /**
   * setPassword
   *
   * @param string $password
   *
   * @return self
   */
  public function setPassword(string $password): self;

  /**
   * getPlainPassword
   *
   * @return string|null
   */
  public function getPlainPassword(): ?string;

  /**
   * setPlainPassword
   *
   * @param string|null $plainPassword
   *
   * @return self
   */
  public function setPlainPassword(?string $plainPassword): self;

  /**
   * getIsActive
   *
   * @return bool
   */
  public function getIsActive(): bool;

  /**
   * setIsActive
   *
   * @param bool $isActive
   *
   * @return self
   */
  public function setIsActive(bool $isActive): self;

  /**
   * getTypeUser
   *
   * @return string
   */
  public function getTypeUser(): string;

  /**
   * setTypeUser
   *
   * @param string $typeUser
   *
   * @return self
   */
  public function setTypeUser(string $typeUser): self;

  /**
   * getInterfaceTheme
   *
   * @return string
   */
  public function getInterfaceTheme(): string;

  /**
   * setInterfaceTheme
   *
   * @param string|null $interfaceTheme
   *
   * @return self
   */
  public function setInterfaceTheme(?string $interfaceTheme): self;

  /**
   * getPasswordResetSelector
   *
   * @return string|null
   */
  public function getPasswordResetSelector(): ?string;

  /**
   * getPasswordResetHash
   *
   * @return string|null
   */
  public function getPasswordResetHash(): ?string;

  /**
   * getPasswordResetExpiresAt
   *
   * @return \DateTimeImmutable|null
   */
  public function getPasswordResetExpiresAt(): ?\DateTimeImmutable;

  /**
   * Stores a password reset request: the selector, the hash of the verifier and the expiry date.
   */
  public function setPasswordReset(string $selector, string $hash, \DateTimeImmutable $expiresAt): self;

  /**
   * clearPasswordReset
   *
   * @return self
   */
  public function clearPasswordReset(): self;
}
