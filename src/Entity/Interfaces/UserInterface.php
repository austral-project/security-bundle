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

  public function isSuperAdmin(): bool;

  public function isAdmin(): bool;

  public function isUser(): bool;

  public function addSecurityRole(RoleInterface $role): self;

  public function removeSecurityRole(RoleInterface $role): self;

  /**
   * @return Collection<int, RoleInterface>
   */
  public function getSecurityRoles(): Collection;

  public function addGroup(GroupInterface $group): self;

  public function removeGroup(GroupInterface $group): self;

  /**
   * @return Collection<int, GroupInterface>
   */
  public function getGroups(): Collection;

  public function getUsername(): string;

  public function setUsername(string $username): self;

  public function getEmail(): string;

  public function setEmail(string $email): self;

  public function getFirstname(): ?string;

  public function setFirstname(?string $firstname): self;

  public function getLastname(): ?string;

  public function setLastname(?string $lastname): self;

  public function getAvatar(): ?string;

  public function setAvatar(?string $avatar): self;

  public function getAvatarColor(): ?string;

  public function setAvatarColor(?string $avatarColor): self;

  public function getLanguage(): ?string;

  public function setLanguage(?string $language): self;

  public function setPassword(string $password): self;

  public function getPlainPassword(): ?string;

  public function setPlainPassword(?string $plainPassword): self;

  public function getIsActive(): bool;

  public function setIsActive(bool $isActive): self;

  public function getTypeUser(): string;

  public function setTypeUser(string $typeUser): self;

  public function getInterfaceTheme(): string;

  public function setInterfaceTheme(?string $interfaceTheme): self;

  public function getPasswordResetSelector(): ?string;

  public function getPasswordResetHash(): ?string;

  public function getPasswordResetExpiresAt(): ?\DateTimeImmutable;

  /**
   * Stores a password reset request: the selector, the hash of the verifier and the expiry date.
   */
  public function setPasswordReset(string $selector, string $hash, \DateTimeImmutable $expiresAt): self;

  public function clearPasswordReset(): self;
}
