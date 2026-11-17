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
use Austral\Contracts\File\FileInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyCoreUserInterface;

/**
 * Austral Interface User.
 *
 * @template T of object
 *
 * @extends EntityInterface<T>
 * @extends FileInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface UserInterface extends SymfonyCoreUserInterface, EntityInterface, PasswordAuthenticatedUserInterface, FileInterface
{

  const string USER_TYPE_ROOT = "root";
  const string USER_TYPE_ADMIN = "admin";
  const string USER_TYPE_USER = "user";

  const string USER_INTERFACE_AUTO = "auto";
  const string USER_INTERFACE_DARK = "dark";
  const string USER_INTERFACE_LIGHT = "light";


  /**
   * @return bool
   */
  public function isSuperAdmin(): bool;
  /**
   * @return bool
   */
  public function isAdmin(): bool;

  /**
   * @return bool
   */
  public function isUser(): bool;

  /**
   * @param RoleInterface<T> $role
   *
   * @return $this
   */
  public function addSecurityRole(RoleInterface $role): self;

  /**
   * @param RoleInterface<T> $role
   *
   * @return $this
   */
  public function removeSecurityRole(RoleInterface $role): self;

  /**
   * Get securityRoles
   * @return Collection<int|string, RoleInterface<T>>
   */
  public function getSecurityRoles(): Collection;

  /**
   * @return list<string>
   */
  public function getRoles(): array;

  /**
   * @param GroupInterface<T> $group
   *
   * @return $this
   */
  public function addGroup(GroupInterface $group): self;

  /**
   * @param GroupInterface<T> $group
   *
   * @return $this
   */
  public function removeGroup(GroupInterface $group): self;

  /**
   * @return Collection<int|string, GroupInterface<T>>
   */
  public function getGroups(): Collection;

  /**
   * @return string
   */
  public function getUsername(): string;

  /**
   * @param string $username
   *
   * @return $this
   */
  public function setUsername(string $username): self;

  /**
   * @return string
   */
  public function getEmail(): string;

  /**
   * @param string $email
   *
   * @return $this
   */
  public function setEmail(string $email): self;

  /**
   * @return string|null
   */
  public function getFirstname(): ?string;

  /**
   * @param string|null $firstname
   *
   * @return $this
   */
  public function setFirstname(?string $firstname): self;

  /**
   * @return string|null
   */
  public function getLastname(): ?string;

  /**
   * @param string|null $lastname
   *
   * @return $this
   */
  public function setLastname(?string $lastname): self;

  /**
   * @return string|null
   */
  public function getAvatar(): ?string;

  /**
   * @param string|null $avatar
   *
   * @return $this
   */
  public function setAvatar(?string $avatar): self;

  /**
   * @return string|null
   */
  public function getAvatarColor(): ?string;

  /**
   * @param string|null $avatarColor
   *
   * @return $this
   */
  public function setAvatarColor(?string $avatarColor): self;

  /**
   * @return string|null
   */
  public function getLanguage(): ?string;

  /**
   * @param string|null $language
   *
   * @return $this
   */
  public function setLanguage(?string $language): self;

  /**
   * @return string|null
   */
  public function getPassword(): ?string;

  /**
   * @param string $password
   *
   * @return $this
   */
  public function setPassword(string $password): self;

  /**
   * @return string|null
   */
  public function getSalt(): ?string;

  /**
   * @param string $salt
   *
   * @return $this
   */
  public function setSalt(string $salt): self;

  /**
   * @return string|null
   */
  public function getPlainPassword(): ?string;

  /**
   * @param string|null $plainPassword
   *
   * @return $this
   */
  public function setPlainPassword(?string $plainPassword): self;

  /**
   * @return bool
   */
  public function getIsActive(): bool;

  /**
   * @param bool $isActive
   *
   * @return $this
   */
  public function setIsActive(bool $isActive): self;

  /**
   * @return string|null
   */
  public function getForgotPasswordToken(): ?string;

  /**
   * @param string|null $forgotPasswordToken
   *
   * @return $this
   */
  public function setForgotPasswordToken(?string $forgotPasswordToken): self;

  /**
   * @return string|null
   */
  public function getTypeUser(): ?string;

  /**
   * @param string|null $typeUser
   *
   * @return $this
   */
  public function setTypeUser(?string $typeUser): self;

  /**
   * @return string|null
   */
  public function getInterfaceTheme(): ?string;

  /**
   * @param string|null $interfaceTheme
   *
   * @return $this
   */
  public function setInterfaceTheme(?string $interfaceTheme): self;
}
