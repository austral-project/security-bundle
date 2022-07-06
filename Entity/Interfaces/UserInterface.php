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

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyCoreUserInterface;

/**
 * Austral Interface User.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface UserInterface extends SymfonyCoreUserInterface
{

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

  public function eraseCredentials();

  /**
   * Add role
   *
   * @param RoleInterface $role
   * @return $this
   */
  public function addSecurityRole(RoleInterface $role): UserInterface;

  /**
   * Remove securityRole
   *
   * @param RoleInterface $role
   *
   * @return $this
   */
  public function removeSecurityRole(RoleInterface $role): UserInterface;

  /**
   * Get securityRoles
   * @return Collection
   */
  public function getSecurityRoles(): Collection;

  /**
   * @return array
   */
  public function getRoles(): array;

  /**
   * Add group
   *
   * @param GroupInterface $group
   * @return $this
   */
  public function addGroup(GroupInterface $group): UserInterface;

  /**
   * Remove group
   *
   * @param GroupInterface $group
   *
   * @return $this
   */
  public function removeGroup(GroupInterface $group): UserInterface;

  /**
   * Get groups
   *
   * @return Collection
   */
  public function getGroups(): Collection;

  /**
   * Get username
   * @return string|null
   */
  public function getUsername(): ?string;

  /**
   * Set username
   *
   * @param string $username
   *
   * @return $this
   */
  public function setUsername(string $username): UserInterface;

  /**
   * Get email
   * @return string|null
   */
  public function getEmail(): ?string;

  /**
   * Set email
   *
   * @param string $email
   *
   * @return $this
   */
  public function setEmail(string $email): UserInterface;

  /**
   * Get firstname
   * @return string|null
   */
  public function getFirstname(): ?string;

  /**
   * Set firstname
   *
   * @param string|null $firstname
   *
   * @return $this
   */
  public function setFirstname(?string $firstname): UserInterface;

  /**
   * Get lastname
   * @return string
   */
  public function getLastname(): ?string;

  /**
   * Set lastname
   *
   * @param string|null $lastname
   *
   * @return $this
   */
  public function setLastname(?string $lastname): UserInterface;

  /**
   * Get avatar
   * @return string
   */
  public function getAvatar(): ?string;

  /**
   * Set avatar
   *
   * @param string|null $avatar
   *
   * @return $this
   */
  public function setAvatar(?string $avatar): UserInterface;

  /**
   * Get avatarColor
   * @return string
   */
  public function getAvatarColor(): ?string;

  /**
   * Set avatarColor
   *
   * @param string|null $avatarColor
   *
   * @return $this
   */
  public function setAvatarColor(?string $avatarColor): UserInterface;

  /**
   * Get language
   * @return string|null
   */
  public function getLanguage(): ?string;

  /**
   * Set language
   *
   * @param string|null $language
   *
   * @return $this
   */
  public function setLanguage(?string $language): UserInterface;

  /**
   * Get password
   * @return string|null
   */
  public function getPassword(): ?string;

  /**
   * Set password
   *
   * @param string $password
   *
   * @return $this
   */
  public function setPassword(string $password): UserInterface;

  /**
   * Get salt
   * @return string|null
   */
  public function getSalt(): ?string;

  /**
   * Set salt
   *
   * @param string $salt
   *
   * @return $this
   */
  public function setSalt(string $salt): UserInterface;

  /**
   * Get plainPassword
   * @return string
   */
  public function getPlainPassword(): ?string;

  /**
   * Set plainPassword
   *
   * @param string|null $plainPassword
   *
   * @return $this
   */
  public function setPlainPassword(?string $plainPassword): UserInterface;

  /**
   * Get isActive
   * @return bool
   */
  public function getIsActive(): bool;

  /**
   * Set isActive
   *
   * @param bool $isActive
   *
   * @return $this
   */
  public function setIsActive(bool $isActive): UserInterface;

  /**
   * Get forgotPasswordToken
   * @return string|null
   */
  public function getForgotPasswordToken(): ?string;

  /**
   * Set forgotPasswordToken
   *
   * @param string|null $forgotPasswordToken
   *
   * @return $this
   */
  public function setForgotPasswordToken(?string $forgotPasswordToken): UserInterface;

  /**
   * Get typeUser
   * @return string|null
   */
  public function getTypeUser(): ?string;

  /**
   * Set typeUser
   *
   * @param string|null $typeUser
   *
   * @return $this
   */
  public function setTypeUser(?string $typeUser): UserInterface;

  /**
   * @return string|null
   */
  public function getInterfaceTheme(): ?string;

  /**
   * @param string|null $interfaceTheme
   *
   * @return $this
   */
  public function setInterfaceTheme(?string $interfaceTheme): UserInterface;
}
