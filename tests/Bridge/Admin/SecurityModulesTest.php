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
use Austral\SecurityBundle\Tests\Fixtures\Entity\Group;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Role;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

/**
 * The modules of the bridge, as an editor who manages the administrators, the groups and the roles without being
 * root (and without the module of the users).
 */
final class SecurityModulesTest extends AdminWebTestCase
{
  protected static function editorRoles(): array
  {
    return [
      'ROLE_SECURITY',
      'ROLE_SECURITY_ADMIN_USERS',
      'ROLE_SECURITY_ADMIN_USERS_EDIT',
      'ROLE_SECURITY_ADMIN_USERS_DELETE',
      'ROLE_SECURITY_ADMIN_USERS_CHANGE',
      'ROLE_SECURITY_GROUPS',
      'ROLE_SECURITY_GROUPS_EDIT',
      'ROLE_SECURITY_GROUPS_CREATE',
      'ROLE_SECURITY_ROLES',
      'ROLE_SECURITY_ROLES_EDIT',
    ];
  }

  public function testTheModulesOfTheBridge(): void
  {
    $this->login('root');
    $crawler = $this->client->request('GET', '/austral-admin/security');

    self::assertResponseIsSuccessful();
    $tiles = $crawler->filter('.austral-admin-tiles a')->each(static fn ($link): string => (string) $link->attr('href'));
    self::assertSame(['/austral-admin/security/admin-users', '/austral-admin/security/users', '/austral-admin/security/groups', '/austral-admin/security/roles'], $tiles);
    foreach ($tiles as $url)
    {
      $this->client->request('GET', $url);
      self::assertResponseIsSuccessful($url);
    }

    $this->client->request('GET', '/austral-admin/security/admin-users/export.csv');
    self::assertResponseIsSuccessful();
    self::assertStringContainsString('root@austral.dev', $this->client->getInternalResponse()->getContent());
  }

  public function testTheUsersModuleShowsTheUsersOnly(): void
  {
    $this->user('member', UserInterface::USER_TYPE_USER);
    $this->login('root');

    $crawler = $this->client->request('GET', '/austral-admin/security/users');
    self::assertResponseIsSuccessful();
    $list = $crawler->filter('tbody')->text();
    self::assertStringContainsString('member@austral.dev', $list);
    self::assertStringNotContainsString('admin@austral.dev', $list);

    $crawler = $this->client->request('GET', '/austral-admin/security/users/new');
    $this->client->submit($crawler->filter('form[name="form"]')->form([
      'form[username]' => 'created',
      'form[email]' => 'created@austral.dev',
      'form[plainPassword][first]' => 'Abcdefg1',
      'form[plainPassword][second]' => 'Abcdefg1',
    ]));
    self::assertResponseRedirects();
    $this->doctrine()->clear();
    self::assertSame(UserInterface::USER_TYPE_USER, $this->findUser('created')->getTypeUser());
  }

  public function testTheRootUsersAreHiddenFromTheOtherAdministrators(): void
  {
    $this->login('editor');
    $crawler = $this->client->request('GET', '/austral-admin/security/admin-users');
    self::assertResponseIsSuccessful();
    $list = $crawler->filter('tbody')->text();
    self::assertStringContainsString('admin@austral.dev', $list);
    self::assertStringNotContainsString('root@austral.dev', $list);

    $root = $this->findUser('root');
    $this->client->request('GET', "/austral-admin/security/admin-users/{$root->getId()}/edit");
    self::assertResponseStatusCodeSame(404);
    $this->client->request('GET', '/austral-admin/security/users');
    self::assertResponseStatusCodeSame(403, 'No role of the users module.');
  }

  public function testTheTypeRootIsNotGivenByAnAdministrator(): void
  {
    $this->login('editor');
    $admin = $this->findUser('admin');
    $crawler = $this->client->request('GET', "/austral-admin/security/admin-users/{$admin->getId()}/edit");
    self::assertResponseIsSuccessful();
    $options = $crawler->filter('select[name="form[typeUser]"] option')->each(static fn ($option): string => (string) $option->attr('value'));
    self::assertNotContains(UserInterface::USER_TYPE_ROOT, $options);

    $values = $crawler->filter('form[name="form"]')->form()->getPhpValues();
    $values['form']['typeUser'] = UserInterface::USER_TYPE_ROOT;
    $this->client->request('POST', "/austral-admin/security/admin-users/{$admin->getId()}/edit", $values);
    self::assertResponseStatusCodeSame(422, 'Not a choice of the field.');
    $this->doctrine()->clear();
    self::assertSame(UserInterface::USER_TYPE_ADMIN, $this->findUser('admin')->getTypeUser());
  }

  public function testNobodyDisablesOrDeletesHisOwnAccount(): void
  {
    $this->login('editor');
    $editor = $this->findUser('editor');
    $crawler = $this->client->request('GET', '/austral-admin/security/admin-users');
    $token = $this->csrfToken();
    self::assertCount(0, $crawler->filter("form[action=\"/austral-admin/security/admin-users/{$editor->getId()}/toggle/isActive?value=0\"]"));

    $this->client->request('POST', "/austral-admin/security/admin-users/{$editor->getId()}/toggle/isActive?value=0", ['_token' => $token]);
    self::assertResponseStatusCodeSame(403);
    $this->client->request('POST', "/austral-admin/security/admin-users/{$editor->getId()}/delete", ['_token' => $token]);
    self::assertResponseStatusCodeSame(403);
    $this->doctrine()->clear();
    self::assertTrue($this->findUser('editor')->getIsActive());

    $admin = $this->findUser('admin');
    $this->client->request('POST', "/austral-admin/security/admin-users/{$admin->getId()}/toggle/isActive?value=0", ['_token' => $token]);
    self::assertResponseRedirects();
    $this->doctrine()->clear();
    self::assertFalse($this->findUser('admin')->getIsActive(), 'Another account is disabled from the list.');
  }

  public function testAGroupTheAdministratorCannotGiveIsKept(): void
  {
    $power = $this->group('power', ['ROLE_SECURITY_USERS_DELETE']);
    $admin = $this->findUser('admin');
    $admin->addGroup($power);
    $this->doctrine()->flush();

    $this->login('editor');
    $crawler = $this->client->request('GET', "/austral-admin/security/admin-users/{$admin->getId()}/edit");
    self::assertResponseIsSuccessful();
    self::assertCount(0, $crawler->filter('input[name="form[groups][]"][value="'.$power->getId().'"]'), 'The group is not offered.');

    $this->client->submit($crawler->filter('form[name="form"]')->form(['form[firstname]' => 'Changed']));
    self::assertResponseRedirects();
    $this->doctrine()->clear();
    $admin = $this->findUser('admin');
    self::assertSame('Changed', $admin->getFirstname());
    self::assertSame(['power'], array_map(static fn ($group): string => $group->getKeyname(), $admin->getGroups()->toArray()), 'The group stays.');
  }

  public function testTheGroupFormShowsTheRolesOfTheUserOnly(): void
  {
    $group = $this->group('team', ['ROLE_SECURITY_USERS_DELETE']);
    $this->login('editor');

    $crawler = $this->client->request('GET', "/austral-admin/security/groups/{$group->getId()}/edit");
    self::assertResponseIsSuccessful();
    self::assertCount(1, $crawler->filter('input[name="form[ROLE_SECURITY_ROLES]"]'));
    self::assertCount(0, $crawler->filter('input[name="form[ROLE_SECURITY_USERS]"]'));
    self::assertCount(0, $crawler->filter('input[name="form[ROLE_SECURITY_USERS_DELETE]"]'));

    $form = $crawler->filter('form[name="form"]')->form();
    self::checkbox($form, 'form[ROLE_SECURITY_ROLES]')->tick();
    $this->client->submit($form);
    self::assertResponseRedirects();
    $this->doctrine()->clear();
    $group = $this->doctrine()->getRepository(Group::class)->findOneBy(['keyname' => 'team']);
    self::assertInstanceOf(Group::class, $group);
    $roles = $group->getRolesArray();
    sort($roles);
    self::assertSame(['ROLE_SECURITY_ROLES', 'ROLE_SECURITY_USERS_DELETE'], $roles, 'The role he does not have stays.');
  }

  public function testRootCreatesTheMissingRolesFromTheGroupForm(): void
  {
    $this->login('root');
    $crawler = $this->client->request('GET', '/austral-admin/security/groups/new');
    self::assertResponseIsSuccessful();
    $form = $crawler->filter('form[name="form"]')->form(['form[name]' => 'User managers']);
    self::checkbox($form, 'form[ROLE_SECURITY_USERS_EDIT]')->tick();
    self::checkbox($form, 'form[ROLE_SECURITY_USERS_DELETE]')->tick();
    $this->client->submit($form);
    self::assertResponseRedirects();

    $this->doctrine()->clear();
    $group = $this->doctrine()->getRepository(Group::class)->findOneBy(['name' => 'User managers']);
    self::assertInstanceOf(Group::class, $group);
    self::assertSame('user_managers', $group->getKeyname());
    $roles = $group->getRolesArray();
    sort($roles);
    self::assertSame(['ROLE_SECURITY_USERS_DELETE', 'ROLE_SECURITY_USERS_EDIT'], $roles);
    $role = $this->doctrine()->getRepository(Role::class)->findOneBy(['role' => 'ROLE_SECURITY_USERS_DELETE']);
    self::assertSame('Users - delete', $role?->getName());
  }

  public function testARoleIsNotTurnedIntoAnotherOne(): void
  {
    $role = $this->doctrine()->getRepository(Role::class)->findOneBy(['role' => 'ROLE_SECURITY_ROLES']);
    self::assertInstanceOf(Role::class, $role);
    $this->login('editor');
    $crawler = $this->client->request('GET', "/austral-admin/security/roles/{$role->getId()}/edit");
    self::assertResponseIsSuccessful();

    $this->client->submit($crawler->filter('form[name="form"]')->form(['form[role]' => 'ROLE_SECURITY_USERS_DELETE']));
    self::assertResponseStatusCodeSame(403);
    $this->doctrine()->clear();
    self::assertNotNull($this->doctrine()->getRepository(Role::class)->findOneBy(['role' => 'ROLE_SECURITY_ROLES']));
  }

  private static function checkbox(Form $form, string $name): ChoiceFormField
  {
    $field = $form[$name];
    self::assertInstanceOf(ChoiceFormField::class, $field);

    return $field;
  }

  /**
   * @param list<string> $roles
   */
  private function group(string $keyname, array $roles): Group
  {
    $group = new Group();
    $group->setName($keyname)->setKeyname($keyname);
    foreach ($roles as $name)
    {
      $role = $this->doctrine()->getRepository(Role::class)->findOneBy(['role' => $name]) ?? new Role()->setRole($name)->setName($name);
      $this->doctrine()->persist($role);
      $group->addRole($role);
    }
    $this->doctrine()->persist($group);
    $this->doctrine()->flush();

    return $group;
  }
}
