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

namespace Austral\SecurityBundle\Tests\Password;

use Austral\SecurityBundle\Event\PasswordResetEvent;
use Austral\SecurityBundle\Event\PasswordResetRequestedEvent;
use Austral\SecurityBundle\Event\SecurityEvents;
use Austral\SecurityBundle\Exception\InvalidPasswordResetTokenException;
use Austral\SecurityBundle\Password\PasswordResetManager;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class PasswordResetManagerTest extends TestCase
{
  private SecurityKit $kit;
  private MockClock $clock;
  private PasswordResetManager $manager;

  /** @var list<PasswordResetRequestedEvent> */
  private array $requested = [];

  /** @var list<PasswordResetEvent> */
  private array $reset = [];

  protected function setUp(): void
  {
    $this->kit = new SecurityKit();
    $this->clock = new MockClock('2026-11-25 10:00:00');
    $this->manager = new PasswordResetManager($this->kit->users, $this->kit->dispatcher, 3600, 60, $this->clock);
    $this->kit->dispatcher->addListener(SecurityEvents::PASSWORD_RESET_REQUESTED, function (PasswordResetRequestedEvent $event): void {
      $this->requested[] = $event;
    });
    $this->kit->dispatcher->addListener(SecurityEvents::PASSWORD_RESET, function (PasswordResetEvent $event): void {
      $this->reset[] = $event;
    });
  }

  public function testALinkIsSentToTheUser(): void
  {
    $user = $this->kit->user('jane@austral.dev', 'secret', 'jane');

    $this->manager->request(' jane ', static fn (string $token): string => "https://austral.dev/reset/{$token}");

    self::assertCount(1, $this->requested);
    $event = $this->requested[0];
    self::assertSame($user, $event->getUser());
    self::assertMatchesRegularExpression('/^[0-9a-f]{72}$/', $event->getToken());
    self::assertSame("https://austral.dev/reset/{$event->getToken()}", $event->getUrl());
    self::assertEquals(new \DateTimeImmutable('2026-11-25 11:00:00'), $event->getExpiresAt());
    self::assertSame(substr($event->getToken(), 0, 24), $user->getPasswordResetSelector());
    self::assertStringNotContainsString(substr($event->getToken(), 24), (string) $user->getPasswordResetHash(), 'Only the hash of the verifier is saved.');
  }

  public function testNothingForAnUnknownOrDisabledUser(): void
  {
    $this->kit->user('jane@austral.dev')->setIsActive(false);
    $this->kit->users->flush();

    $this->manager->request('jane@austral.dev', $this->url(...));
    $this->manager->request('john@austral.dev', $this->url(...));

    self::assertSame([], $this->requested, 'Same answer for everyone: no user enumeration.');
  }

  public function testOneLinkPerRequestInterval(): void
  {
    $this->kit->user('jane@austral.dev');

    $this->manager->request('jane@austral.dev', $this->url(...));
    $this->clock->sleep(59);
    $this->manager->request('jane@austral.dev', $this->url(...));
    self::assertCount(1, $this->requested);

    $this->clock->sleep(2);
    $this->manager->request('jane@austral.dev', $this->url(...));
    self::assertCount(2, $this->requested);
    self::assertNull($this->manager->verify($this->requested[0]->getToken()), 'A new link replaces the previous one.');
    self::assertNotNull($this->manager->verify($this->requested[1]->getToken()));
  }

  public function testAValidToken(): void
  {
    $user = $this->kit->user('jane@austral.dev');
    $token = $this->token('jane@austral.dev');
    $this->kit->clear();

    self::assertSame($user->getId(), $this->manager->verify($token)?->getId());
  }

  public function testAnInvalidToken(): void
  {
    $this->kit->user('jane@austral.dev');
    $token = $this->token('jane@austral.dev');
    $selector = substr($token, 0, 24);

    self::assertNull($this->manager->verify(''));
    self::assertNull($this->manager->verify('not-a-token'));
    self::assertNull($this->manager->verify(strtoupper($token)));
    self::assertNull($this->manager->verify($selector.str_repeat('0', 48)), 'Wrong verifier.');
    self::assertNull($this->manager->verify(str_repeat('0', 24).substr($token, 24)), 'Wrong selector.');
  }

  public function testAnExpiredToken(): void
  {
    $this->kit->user('jane@austral.dev');
    $token = $this->token('jane@austral.dev');

    $this->clock->sleep(3599);
    self::assertNotNull($this->manager->verify($token));
    $this->clock->sleep(1);
    self::assertNull($this->manager->verify($token));
  }

  public function testTheTokenOfADisabledUser(): void
  {
    $user = $this->kit->user('jane@austral.dev');
    $token = $this->token('jane@austral.dev');
    $user->setIsActive(false);
    $this->kit->users->updateUser($user);

    self::assertNull($this->manager->verify($token));
  }

  public function testThePasswordIsReset(): void
  {
    $this->kit->user('jane@austral.dev', 'old');
    $token = $this->token('jane@austral.dev');
    $this->kit->clear();

    $user = $this->manager->reset($token, 'new');

    self::assertCount(1, $this->reset);
    self::assertSame($user, $this->reset[0]->getUser());
    $this->kit->clear();
    $saved = $this->kit->users->retrieveUserById($user->getId());
    self::assertInstanceOf(User::class, $saved);
    self::assertTrue($this->kit->hasher->isPasswordValid($saved, 'new'));
    self::assertNull($saved->getPasswordResetSelector());
    self::assertNull($saved->getPasswordResetHash());
    self::assertNull($saved->getPasswordResetExpiresAt());
    self::assertNull($this->manager->verify($token), 'A token is used once.');
  }

  public function testNoResetWithAnInvalidToken(): void
  {
    $this->expectException(InvalidPasswordResetTokenException::class);
    $this->manager->reset(str_repeat('0', 72), 'new');
  }

  public function testNoEmptyPassword(): void
  {
    $this->kit->user('jane@austral.dev');
    $token = $this->token('jane@austral.dev');

    $this->expectException(\InvalidArgumentException::class);
    $this->manager->reset($token, '');
  }

  public function testTheLifetime(): void
  {
    self::assertSame(3600, $this->manager->getLifetime());

    $this->expectException(\InvalidArgumentException::class);
    new PasswordResetManager($this->kit->users, $this->kit->dispatcher, 0);
  }

  private function token(string $login): string
  {
    $this->manager->request($login, $this->url(...));
    $event = end($this->requested);
    self::assertInstanceOf(PasswordResetRequestedEvent::class, $event);

    return $event->getToken();
  }

  private function url(string $token): string
  {
    return "https://austral.dev/reset/{$token}";
  }
}
