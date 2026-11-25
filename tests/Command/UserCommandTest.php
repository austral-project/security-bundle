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

namespace Austral\SecurityBundle\Tests\Command;

use Austral\SecurityBundle\Command\UserCommand;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class UserCommandTest extends TestCase
{
  private SecurityKit $kit;
  private CommandTester $tester;

  protected function setUp(): void
  {
    $this->kit = new SecurityKit();
    $this->tester = new CommandTester(new UserCommand($this->kit->users));
  }

  public function testARootIsCreated(): void
  {
    $status = $this->tester->execute(['--email' => 'support@austral.dev', '--password' => 'secret', '--root' => true, '--create' => true]);

    self::assertSame(Command::SUCCESS, $status, $this->tester->getDisplay());
    self::assertStringContainsString('The user support@austral.dev is created.', $this->tester->getDisplay());
    $this->kit->clear();
    $user = $this->kit->users->retrieveByEmail('support@austral.dev');
    self::assertNotNull($user);
    self::assertSame('support@austral.dev', $user->getUsername());
    self::assertSame(UserInterface::USER_TYPE_ROOT, $user->getTypeUser());
    self::assertTrue($user->getIsActive());
    self::assertTrue($this->kit->hasher->isPasswordValid($user, 'secret'));
  }

  public function testThePasswordIsAskedHidden(): void
  {
    $this->tester->setInputs(['secret']);
    $status = $this->tester->execute(['--email' => 'jane@austral.dev', '--username' => 'jane', '--admin' => true], ['interactive' => true]);

    self::assertSame(Command::SUCCESS, $status, $this->tester->getDisplay());
    self::assertStringNotContainsString('secret', $this->tester->getDisplay());
    $this->kit->clear();
    $user = $this->kit->users->retrieveByLogin('jane');
    self::assertNotNull($user);
    self::assertSame(UserInterface::USER_TYPE_ADMIN, $user->getTypeUser());
    self::assertTrue($this->kit->hasher->isPasswordValid($user, 'secret'));
  }

  public function testThePasswordOfAUserIsUpdated(): void
  {
    $jane = $this->kit->user('jane@austral.dev', 'old');

    $status = $this->tester->execute(['--email' => 'jane@austral.dev', '--password' => 'new', '--disabled' => true]);

    self::assertSame(Command::SUCCESS, $status, $this->tester->getDisplay());
    self::assertStringContainsString('The user jane@austral.dev is updated.', $this->tester->getDisplay());
    $this->kit->clear();
    $user = $this->kit->users->retrieveUserById($jane->getId());
    self::assertNotNull($user);
    self::assertFalse($user->getIsActive());
    self::assertSame(UserInterface::USER_TYPE_USER, $user->getTypeUser(), 'The type is kept.');
    self::assertTrue($this->kit->hasher->isPasswordValid($user, 'new'));
  }

  public function testCreateAnExistingUser(): void
  {
    $this->kit->user('jane@austral.dev', 'old');

    self::assertSame(Command::FAILURE, $this->tester->execute(['--email' => 'jane@austral.dev', '--password' => 'new', '--create' => true]));
    self::assertStringContainsString('already exists', $this->tester->getDisplay());

    self::assertSame(Command::SUCCESS, $this->tester->execute(['--email' => 'jane@austral.dev', '--password' => 'new', '--create' => true, '--if-not-exist' => true]));
    $this->kit->clear();
    $user = $this->kit->users->retrieveByEmail('jane@austral.dev');
    self::assertNotNull($user);
    self::assertTrue($this->kit->hasher->isPasswordValid($user, 'old'), 'The user is left untouched.');
  }

  public function testInvalidOptions(): void
  {
    self::assertSame(Command::INVALID, $this->tester->execute(['--password' => 'secret']));
    self::assertStringContainsString('--email is required', $this->tester->getDisplay());

    self::assertSame(Command::INVALID, $this->tester->execute(['--email' => 'jane@austral.dev', '--password' => 'secret', '--root' => true, '--admin' => true]));
    self::assertNull($this->kit->users->retrieveByEmail('jane@austral.dev'));
  }

  public function testAnEmptyPasswordIsRefused(): void
  {
    $this->tester->setInputs(['', '', '']);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The password cannot be empty.');
    $this->tester->execute(['--email' => 'jane@austral.dev'], ['interactive' => true]);
  }
}
