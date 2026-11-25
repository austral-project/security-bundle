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

use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use PHPUnit\Framework\TestCase;

final class PasswordUpdaterTest extends TestCase
{
  public function testThePlainPasswordIsHashedThenCleared(): void
  {
    $kit = new SecurityKit();
    $user = new User()->setPlainPassword('secret');
    $user->setPasswordReset('selector', 'hash', new \DateTimeImmutable('+1 hour'));

    self::assertTrue($kit->passwordUpdater->update($user));
    self::assertNull($user->getPlainPassword());
    self::assertNull($user->getPasswordResetSelector(), 'A new password ends a password reset.');
    self::assertStringStartsWith('$2y$', (string) $user->getPassword());
    self::assertTrue($kit->hasher->isPasswordValid($user, 'secret'));
    self::assertFalse($kit->hasher->isPasswordValid($user, 'secret@'), 'No salt appended anymore.');
  }

  public function testNothingWithoutPlainPassword(): void
  {
    $kit = new SecurityKit();
    $user = new User()->setPassword('$2y$hash')->setPlainPassword('');

    self::assertFalse($kit->passwordUpdater->update($user));
    self::assertSame('$2y$hash', $user->getPassword());
  }

  public function testTheListenerHashesWhenTheUserIsSaved(): void
  {
    $kit = new SecurityKit();
    $user = $kit->user('jane@austral.dev', 'secret');
    $kit->clear();

    $saved = $kit->users->retrieveUserById($user->getId());
    self::assertNotNull($saved);
    self::assertTrue($kit->hasher->isPasswordValid($saved, 'secret'));

    // Saved again without plain password: the hash is kept.
    $hash = $saved->getPassword();
    $kit->users->updateUser($saved);
    self::assertSame($hash, $saved->getPassword());
  }
}
