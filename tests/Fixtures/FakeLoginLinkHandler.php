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

namespace Austral\SecurityBundle\Tests\Fixtures;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

/**
 * Login link handler of a firewall, without router nor signature.
 */
final class FakeLoginLinkHandler implements LoginLinkHandlerInterface
{
  /** @var list<array{user: UserInterface, request: Request|null}> */
  public array $created = [];

  public function createLoginLink(UserInterface $user, ?Request $request = null, ?int $lifetime = null): LoginLinkDetails
  {
    $this->created[] = ['user' => $user, 'request' => $request];

    return new LoginLinkDetails("https://austral.dev/admin/login-link?user={$user->getUserIdentifier()}", new \DateTimeImmutable('2026-11-25 10:10:00'));
  }

  public function consumeLoginLink(Request $request): UserInterface
  {
    throw new \LogicException('Not used by the tests.');
  }
}
