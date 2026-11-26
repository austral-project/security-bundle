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

namespace Austral\SecurityBundle\Tests\Bridge\Admin;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Tests\Fixtures\AdminKernel;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Group;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Role;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The test application on an empty database, with three users: root, an administrator without group and an
 * editor whose group gives the roles listed in editorRoles().
 */
abstract class AdminWebTestCase extends WebTestCase
{
  protected KernelBrowser $client;

  /**
   * @param array<string, mixed> $options
   */
  protected static function createKernel(array $options = []): KernelInterface
  {
    return new AdminKernel('test', false, static::adminConfig());
  }

  /**
   * @return array<string, mixed>
   */
  protected static function adminConfig(): array
  {
    return [];
  }

  /**
   * @return list<string>
   */
  protected static function editorRoles(): array
  {
    return ['ROLE_SECURITY'];
  }

  protected function setUp(): void
  {
    $this->client = static::createClient();
    $this->client->disableReboot();
    $doctrine = $this->doctrine();
    $schemaTool = new SchemaTool($doctrine);
    $metadata = $doctrine->getMetadataFactory()->getAllMetadata();
    $schemaTool->dropSchema($metadata);
    $schemaTool->createSchema($metadata);

    $this->user('root', UserInterface::USER_TYPE_ROOT);
    $this->user('admin', UserInterface::USER_TYPE_ADMIN);
    $editor = $this->user('editor', UserInterface::USER_TYPE_ADMIN);
    $group = new Group();
    $group->setName('Editors')->setKeyname('editors');
    foreach (static::editorRoles() as $name)
    {
      $role = new Role();
      $role->setRole($name)->setName($name);
      $doctrine->persist($role);
      $group->addRole($role);
    }
    $doctrine->persist($group);
    $editor->addGroup($group);
    $doctrine->flush();
  }

  protected function doctrine(): EntityManagerInterface
  {
    /* @var EntityManagerInterface */
    return static::getContainer()->get('doctrine.orm.entity_manager');
  }

  protected function user(string $username, string $type): User
  {
    $user = new User();
    $user->setUsername($username)->setEmail("{$username}@austral.dev")->setIsActive(true)->setTypeUser($type)->setLanguage('en');
    $user->setPassword('secret');
    $this->doctrine()->persist($user);
    $this->doctrine()->flush();

    return $user;
  }

  protected function findUser(string $username): User
  {
    $user = $this->doctrine()->getRepository(User::class)->findOneBy(['username' => $username]);
    self::assertInstanceOf(User::class, $user);

    return $user;
  }

  protected function login(string $username): void
  {
    $this->client->loginUser($this->findUser($username), 'austral_admin');
  }

  /**
   * CSRF token of the admin, read from a page of the admin.
   */
  protected function csrfToken(string $url = '/austral-admin/'): string
  {
    $crawler = $this->client->request('GET', $url);
    $meta = $crawler->filter('meta[name="austral-admin-csrf-token"]');
    self::assertGreaterThan(0, $meta->count(), "No CSRF token on {$url}.");

    return (string) $meta->first()->attr('content');
  }

  /**
   * FrameworkBundle::boot() registers the ErrorHandler of Symfony as exception handler (without symfony/runtime):
   * it is removed after the test, PHPUnit fails a test that leaves a handler (risky).
   */
  protected function tearDown(): void
  {
    parent::tearDown();
    self::restoreExceptionHandlers();
  }

  private static function restoreExceptionHandlers(): void
  {
    while (true)
    {
      $handler = set_exception_handler(static fn (\Throwable $exception) => null);
      restore_exception_handler();
      if (!\is_array($handler) || !$handler[0] instanceof ErrorHandler)
      {
        return;
      }
      restore_exception_handler();
    }
  }
}
