<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Austral\SecurityBundle\Tests\Entity;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Group;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Role;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
  public function testANewUser(): void
  {
    $user = new User();

    self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/', $user->getId());
    self::assertSame(UserInterface::USER_TYPE_USER, $user->getTypeUser());
    self::assertTrue($user->isUser());
    self::assertFalse($user->getIsActive());
    self::assertSame(UserInterface::USER_INTERFACE_AUTO, $user->getInterfaceTheme());
    self::assertSame($user->getId(), $user->getUserIdentifier(), 'Without username the id identifies the user.');
  }

  public function testTheRoleOfTheTypeOfUser(): void
  {
    $user = new User();
    self::assertSame([UserInterface::ROLE_USER_ACCESS], $user->getRoles());

    $user->setTypeUser(UserInterface::USER_TYPE_ADMIN);
    self::assertTrue($user->isAdmin());
    self::assertSame([UserInterface::ROLE_ADMIN_ACCESS], $user->getRoles());

    $user->setTypeUser(UserInterface::USER_TYPE_ROOT);
    self::assertTrue($user->isSuperAdmin());
    self::assertSame([UserInterface::ROLE_ROOT], $user->getRoles());
  }

  public function testAnUnknownTypeOfUser(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown type of user "god".');
    new User()->setTypeUser('god');
  }

  public function testTheRolesOfTheUserAndOfItsGroups(): void
  {
    $edit = new Role()->setRole('ROLE_EDIT')->setName('Edit');
    $publish = new Role()->setRole('ROLE_PUBLISH')->setName('Publish');
    $editors = new Group()->setKeyname('editors')->setName('Editors');
    $editors->addRole($edit)->addRole($publish);

    $user = new User();
    $user->addSecurityRole($edit)->addGroup($editors);

    self::assertSame([UserInterface::ROLE_USER_ACCESS, 'ROLE_EDIT', 'ROLE_PUBLISH'], $user->getRoles(), 'Each role once.');
    self::assertSame(['ROLE_EDIT', 'ROLE_PUBLISH'], $editors->getRolesArray());
  }

  public function testTheAssociationsAreSyncedBothWays(): void
  {
    $role = new Role()->setRole('ROLE_EDIT')->setName('Edit');
    $group = new Group()->setKeyname('editors')->setName('Editors');
    $user = new User();

    $user->addSecurityRole($role)->addSecurityRole($role)->addGroup($group);
    self::assertCount(1, $user->getSecurityRoles());
    self::assertTrue($role->getUsers()->contains($user));
    self::assertTrue($group->getUsers()->contains($user));

    $group->addRole($role);
    self::assertTrue($role->getGroups()->contains($group));

    $user->removeSecurityRole($role)->removeGroup($group);
    $group->removeRole($role);
    self::assertCount(0, $role->getUsers());
    self::assertCount(0, $group->getUsers());
    self::assertCount(0, $role->getGroups());
    self::assertSame([UserInterface::ROLE_USER_ACCESS], $user->getRoles());
  }

  public function testTheName(): void
  {
    $user = new User()->setEmail('jane@austral.dev');
    self::assertSame('jane@austral.dev', (string) $user);

    $user->setFirstname('Jane')->setLastname('Doe');
    self::assertSame('Doe Jane', (string) $user);
  }

  public function testTheSessionKeepsNeitherThePasswordNorTheResetToken(): void
  {
    $user = $this->user();
    $user->setPasswordReset('selector', 'hash', new \DateTimeImmutable('+1 hour'));

    $serialized = serialize($user);
    self::assertStringNotContainsString('$2y$hash', $serialized);
    self::assertStringNotContainsString('selector', $serialized);

    $fromSession = unserialize($serialized);
    self::assertInstanceOf(User::class, $fromSession);
    self::assertSame($user->getId(), $fromSession->getId());
    self::assertSame('jane', $fromSession->getUserIdentifier());
    self::assertSame('jane@austral.dev', $fromSession->getEmail());
    self::assertSame(UserInterface::USER_TYPE_ADMIN, $fromSession->getTypeUser());
    self::assertTrue($fromSession->getIsActive());
    self::assertSame('', $fromSession->getPassword(), 'The hash is not in the session.');
  }

  public function testTheUserOfTheSessionIsTheUserOfTheDatabase(): void
  {
    $user = $this->user();
    $fromSession = unserialize(serialize($user));
    self::assertInstanceOf(User::class, $fromSession);

    self::assertTrue($fromSession->isEqualTo($user));
    self::assertFalse($fromSession->isEqualTo(new User()), 'Another user.');
  }

  public function testTheUserIsLoggedOutWhenItsPasswordChanges(): void
  {
    $user = $this->user();
    $fromSession = unserialize(serialize($user));
    self::assertInstanceOf(User::class, $fromSession);

    $user->setPassword('$2y$other');
    self::assertFalse($fromSession->isEqualTo($user));
  }

  public function testTheUserIsLoggedOutWhenItIsDisabled(): void
  {
    $user = $this->user();
    $fromSession = unserialize(serialize($user));
    self::assertInstanceOf(User::class, $fromSession);

    $user->setIsActive(false);
    self::assertFalse($fromSession->isEqualTo($user));
  }

  public function testTheUserIsLoggedOutWhenItsUsernameChanges(): void
  {
    $user = $this->user();
    $fromSession = unserialize(serialize($user));
    self::assertInstanceOf(User::class, $fromSession);

    $user->setUsername('john');
    self::assertFalse($fromSession->isEqualTo($user));
  }

  public function testThePasswordReset(): void
  {
    $user = new User();
    $expiresAt = new \DateTimeImmutable('+1 hour');
    $user->setPasswordReset('selector', 'hash', $expiresAt);

    self::assertSame('selector', $user->getPasswordResetSelector());
    self::assertSame('hash', $user->getPasswordResetHash());
    self::assertSame($expiresAt, $user->getPasswordResetExpiresAt());

    $user->clearPasswordReset();
    self::assertNull($user->getPasswordResetSelector());
    self::assertNull($user->getPasswordResetHash());
    self::assertNull($user->getPasswordResetExpiresAt());
  }

  private function user(): User
  {
    $user = new User();
    $user->setUsername('jane')->setEmail('jane@austral.dev')->setPassword('$2y$hash')->setIsActive(true)->setTypeUser(UserInterface::USER_TYPE_ADMIN);

    return $user;
  }
}
