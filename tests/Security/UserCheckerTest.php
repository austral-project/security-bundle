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

use Austral\SecurityBundle\Security\UserChecker;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class UserCheckerTest extends TestCase
{
  public function testAnActiveUserLogsIn(): void
  {
    $this->expectNotToPerformAssertions();
    $checker = new UserChecker();
    $user = new User()->setIsActive(true);

    $checker->checkPreAuth($user);
    $checker->checkPostAuth($user);
    $checker->checkPreAuth(new InMemoryUser('jane', null, [], false));
  }

  public function testADisabledUserDoesNotLogIn(): void
  {
    $user = new User()->setIsActive(false);
    try
    {
      new UserChecker()->checkPreAuth($user);
      self::fail('DisabledException expected.');
    }
    catch (DisabledException $exception)
    {
      self::assertSame($user, $exception->getUser());
    }
  }
}
