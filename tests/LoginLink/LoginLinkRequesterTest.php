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

namespace Austral\SecurityBundle\Tests\LoginLink;

use Austral\SecurityBundle\Event\LoginLinkRequestedEvent;
use Austral\SecurityBundle\Event\SecurityEvents;
use Austral\SecurityBundle\LoginLink\LoginLinkRequester;
use Austral\SecurityBundle\Tests\Fixtures\FakeLoginLinkHandler;
use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class LoginLinkRequesterTest extends TestCase
{
  private SecurityKit $kit;
  private FakeLoginLinkHandler $handler;

  /** @var list<LoginLinkRequestedEvent> */
  private array $requested = [];

  protected function setUp(): void
  {
    $this->kit = new SecurityKit();
    $this->handler = new FakeLoginLinkHandler();
    $this->kit->dispatcher->addListener(SecurityEvents::LOGIN_LINK_REQUESTED, function (LoginLinkRequestedEvent $event): void {
      $this->requested[] = $event;
    });
  }

  public function testALinkIsSentToTheUser(): void
  {
    $user = $this->kit->user('jane@austral.dev', 'secret', 'jane');
    $request = Request::create('https://austral.dev/admin/login');
    $requester = new LoginLinkRequester($this->kit->users, $this->kit->dispatcher, $this->handler);

    self::assertTrue($requester->isEnabled());
    $requester->request(' jane@austral.dev ', $request);

    self::assertSame([['user' => $user, 'request' => $request]], $this->handler->created);
    self::assertCount(1, $this->requested);
    self::assertSame($user, $this->requested[0]->getUser());
    self::assertSame('https://austral.dev/admin/login-link?user=jane', $this->requested[0]->getUrl());
    self::assertEquals(new \DateTimeImmutable('2026-11-25 10:10:00'), $this->requested[0]->getExpiresAt());
  }

  public function testNothingForAnUnknownOrDisabledUser(): void
  {
    $this->kit->user('jane@austral.dev')->setIsActive(false);
    $this->kit->users->flush();
    $requester = new LoginLinkRequester($this->kit->users, $this->kit->dispatcher, $this->handler);

    $requester->request('jane@austral.dev');
    $requester->request('john@austral.dev');

    self::assertSame([], $this->handler->created);
    self::assertSame([], $this->requested);
  }

  public function testTheFirewallHasNoLoginLink(): void
  {
    $requester = new LoginLinkRequester($this->kit->users, $this->kit->dispatcher, null, 'main');
    self::assertFalse($requester->isEnabled());

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('add "login_link" to the firewall "main"');
    $requester->request('jane@austral.dev');
  }

  public function testNoFirewall(): void
  {
    $requester = new LoginLinkRequester($this->kit->users, $this->kit->dispatcher);
    self::assertFalse($requester->isEnabled());

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('set "austral_security.login_link.firewall"');
    $requester->request('jane@austral.dev');
  }
}
