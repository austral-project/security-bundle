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

namespace Austral\SecurityBundle\Tests\Repository;

use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use Doctrine\ORM\PersistentCollection;
use PHPUnit\Framework\TestCase;

final class RepositoryTest extends TestCase
{
  private SecurityKit $kit;

  protected function setUp(): void
  {
    $this->kit = new SecurityKit();
  }

  public function testALoginIsAnEmailOrAUsername(): void
  {
    $jane = $this->kit->user('Jane@Austral.dev', 'secret', 'jane');
    $this->kit->clear();

    self::assertSame($jane->getId(), $this->kit->users->retrieveByLogin('jane@austral.dev')?->getId(), 'The email is case insensitive.');
    self::assertSame($jane->getId(), $this->kit->users->retrieveByLogin('jane')?->getId());
    self::assertNull($this->kit->users->retrieveByLogin('JANE'), 'The username is case sensitive.');
    self::assertNull($this->kit->users->retrieveByLogin(''));
    self::assertNull($this->kit->users->retrieveByLogin('john'));
  }

  public function testTheEmailWinsOverAUsername(): void
  {
    // The username of a user is the email of another one.
    $this->kit->user('john@austral.dev', 'secret', 'jane@austral.dev');
    $jane = $this->kit->user('jane@austral.dev', 'secret', 'jane');
    $this->kit->clear();

    self::assertSame($jane->getId(), $this->kit->users->retrieveByLogin('jane@austral.dev')?->getId());
  }

  public function testTheUserComesWithItsGroupsAndRoles(): void
  {
    $edit = $this->kit->role('ROLE_EDIT');
    $publish = $this->kit->role('ROLE_PUBLISH');
    $editors = $this->kit->group('editors');
    $editors->addRole($publish);
    $user = $this->kit->user();
    $user->addSecurityRole($edit)->addGroup($editors);
    $this->kit->users->updateUser($user);
    $this->kit->clear();

    $found = $this->kit->users->retrieveUserById($user->getId());
    self::assertNotNull($found);
    $groups = $found->getGroups();
    self::assertInstanceOf(PersistentCollection::class, $groups);
    self::assertTrue($groups->isInitialized(), 'Fetch-joined.');
    self::assertSame(['ROLE_USER_ACCESS', 'ROLE_EDIT', 'ROLE_PUBLISH'], $found->getRoles());
  }

  public function testTheJoinTableOfTheGroupsHasAGroupIdColumn(): void
  {
    $user = $this->kit->user();
    $user->addGroup($this->kit->group('editors'));
    $this->kit->users->updateUser($user);

    $columns = array_keys($this->kit->doctrine->getConnection()->createSchemaManager()->listTableColumns('austral_security_user_group'));
    sort($columns);
    self::assertSame(['group_id', 'user_id'], $columns);
  }

  public function testTheOtherRetrievals(): void
  {
    $user = $this->kit->user('jane@austral.dev');
    $user->setPasswordReset('0123456789abcdef01234567', 'hash', new \DateTimeImmutable('+1 hour'));
    $this->kit->users->updateUser($user);
    $this->kit->clear();

    self::assertSame($user->getId(), $this->kit->users->retrieveByEmail('JANE@austral.dev')?->getId());
    self::assertNull($this->kit->users->retrieveByEmail('john@austral.dev'));
    self::assertNull($this->kit->users->retrieveUserById('unknown'));
    self::assertSame($user->getId(), $this->kit->users->retrieveByPasswordResetSelector('0123456789abcdef01234567')?->getId());
    self::assertNull($this->kit->users->retrieveByPasswordResetSelector('unknown'));
  }

  public function testTheRolesByRole(): void
  {
    $publish = $this->kit->role('ROLE_PUBLISH');
    $edit = $this->kit->role('ROLE_EDIT');
    $this->kit->clear();

    $roles = $this->kit->roles->selectRoles();
    self::assertSame(['ROLE_EDIT', 'ROLE_PUBLISH'], array_keys($roles));
    self::assertSame($edit->getId(), $roles['ROLE_EDIT']->getId());
    self::assertSame($publish->getId(), $roles['ROLE_PUBLISH']->getId());
  }

  public function testAGroupByKeyname(): void
  {
    $editors = $this->kit->group('editors');
    $this->kit->clear();

    self::assertSame($editors->getId(), $this->kit->groups->retrieveByKeyname('editors')?->getId());
    self::assertNull($this->kit->groups->retrieveByKeyname('authors'));
  }

  public function testTheEntityManagersCreateTheirEntities(): void
  {
    $user = $this->kit->users->create(['email' => 'jane@austral.dev', 'username' => 'jane']);
    self::assertSame('jane@austral.dev', $user->getEmail());
    self::assertSame('jane', $user->getUsername());
    self::assertSame('ROLE_EDIT', $this->kit->roles->create(['role' => 'ROLE_EDIT'])->getRole());
    self::assertSame('editors', $this->kit->groups->create(['keyname' => 'editors'])->getKeyname());
  }
}
