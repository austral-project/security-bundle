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

namespace Austral\SecurityBundle\Tests\Security;

use Austral\SecurityBundle\Security\UserProvider;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class UserProviderTest extends TestCase
{
  private SecurityKit $kit;
  private UserProvider $provider;

  protected function setUp(): void
  {
    $this->kit = new SecurityKit();
    $this->provider = new UserProvider($this->kit->users);
  }

  public function testAUserByEmailUsernameOrId(): void
  {
    $jane = $this->kit->user('jane@austral.dev', 'secret', 'jane');
    $this->kit->clear();

    self::assertSame($jane->getId(), $this->provider->loadUserByIdentifier('jane@austral.dev')->getId());
    self::assertSame($jane->getId(), $this->provider->loadUserByIdentifier('jane')->getId());
    // The identifier of a login link or a remember-me cookie of a user without username.
    self::assertSame($jane->getId(), $this->provider->loadUserByIdentifier($jane->getId())->getId());
  }

  public function testAnUnknownUser(): void
  {
    try
    {
      $this->provider->loadUserByIdentifier('john');
      self::fail('UserNotFoundException expected.');
    }
    catch (UserNotFoundException $exception)
    {
      self::assertSame('john', $exception->getUserIdentifier());
    }
  }

  public function testTheUserIsRefreshedById(): void
  {
    $jane = $this->kit->user('jane@austral.dev', 'secret', 'jane');
    $fromSession = unserialize(serialize($jane));
    self::assertInstanceOf(User::class, $fromSession);
    // The username changed since the login.
    $jane->setUsername('jane.doe');
    $this->kit->users->updateUser($jane);
    $this->kit->clear();

    $refreshed = $this->provider->refreshUser($fromSession);
    self::assertSame($jane->getId(), $refreshed->getId());
    self::assertSame('jane.doe', $refreshed->getUserIdentifier());
  }

  public function testADisabledUserIsRefreshed(): void
  {
    // Not a 500 error: the user is reloaded and isEqualTo() logs it out.
    $jane = $this->kit->user();
    $fromSession = unserialize(serialize($jane));
    self::assertInstanceOf(User::class, $fromSession);
    $jane->setIsActive(false);
    $this->kit->users->updateUser($jane);
    $this->kit->clear();

    $refreshed = $this->provider->refreshUser($fromSession);
    self::assertFalse($refreshed->getIsActive());
    self::assertFalse($fromSession->isEqualTo($refreshed));
  }

  public function testADeletedUserIsNotRefreshed(): void
  {
    $jane = $this->kit->user();
    $fromSession = unserialize(serialize($jane));
    self::assertInstanceOf(User::class, $fromSession);
    $this->kit->users->delete($jane);

    $this->expectException(UserNotFoundException::class);
    $this->provider->refreshUser($fromSession);
  }

  public function testTheUserOfA31SessionIsLoggedOut(): void
  {
    $this->kit->user();
    // Session written by 3.1: the properties, without __serialize().
    $fromSession = new \ReflectionClass(User::class)->newInstanceWithoutConstructor();
    $fromSession->__unserialize(["\0*\0id" => 'f0e1d2c3', "\0*\0username" => 'jane', "\0*\0salt" => 'salt']);

    $this->expectException(UserNotFoundException::class);
    $this->provider->refreshUser($fromSession);
  }

  public function testAnotherClassOfUserIsNotSupported(): void
  {
    self::assertTrue($this->provider->supportsClass(User::class));
    self::assertFalse($this->provider->supportsClass(InMemoryUser::class));

    $this->expectException(UnsupportedUserException::class);
    $this->provider->refreshUser(new InMemoryUser('jane', 'secret'));
  }

  public function testThePasswordIsUpgraded(): void
  {
    $jane = $this->kit->user();
    $this->provider->upgradePassword($jane, '$2y$13$upgraded');
    $this->kit->clear();

    self::assertSame('$2y$13$upgraded', $this->kit->users->retrieveUserById($jane->getId())?->getPassword());
  }
}
