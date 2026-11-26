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

namespace Austral\SecurityBundle\Tests\DependencyInjection;

use Austral\AdminBundle\AustralAdminBundle;
use Austral\AdminBundle\DependencyInjection\AustralAdminExtension;
use Austral\SecurityBundle\Bridge\Admin\AdminUserAdmin;
use Austral\SecurityBundle\Bridge\Admin\GroupAdmin;
use Austral\SecurityBundle\Bridge\Admin\RoleAdmin;
use Austral\SecurityBundle\Bridge\Admin\UserAdmin;
use Austral\SecurityBundle\Command\UserCommand;
use Austral\SecurityBundle\DependencyInjection\AustralSecurityExtension;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\EntityManager\GroupEntityManager;
use Austral\SecurityBundle\EntityManager\RoleEntityManager;
use Austral\SecurityBundle\EntityManager\UserEntityManager;
use Austral\SecurityBundle\Listener\PasswordListener;
use Austral\SecurityBundle\LoginLink\LoginLinkRequester;
use Austral\SecurityBundle\Password\PasswordResetManager;
use Austral\SecurityBundle\Password\PasswordUpdater;
use Austral\SecurityBundle\Security\UserChecker;
use Austral\SecurityBundle\Security\UserProvider;
use Austral\SecurityBundle\Security\Voter\RootVoter;
use Austral\SecurityBundle\Tests\Fixtures\DoctrineExtensionStub;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Group;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Role;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Austral\SecurityBundle\Tests\Fixtures\FakeLoginLinkHandler;
use Austral\SecurityBundle\Tests\Fixtures\SecurityKit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class AustralSecurityExtensionTest extends TestCase
{
  private const array CONFIG = [
    'entities' => ['user' => User::class, 'role' => Role::class, 'group' => Group::class],
    'password_reset' => ['lifetime' => 900, 'request_interval' => 30],
  ];

  public function testTheServicesAreExplicit(): void
  {
    $container = $this->load();

    foreach ($container->getDefinitions() as $id => $definition)
    {
      if (str_starts_with($id, 'Austral\\'))
      {
        self::assertFalse($definition->isAutowired(), "{$id} is autowired.");
        self::assertFalse($definition->isAutoconfigured(), "{$id} is autoconfigured.");
        self::assertFalse($definition->isPublic(), "{$id} is public: only the austral.* aliases are.");
      }
    }
    self::assertSame(['security.voter' => [['priority' => 1000]]], $container->getDefinition(RootVoter::class)->getTags());
    self::assertSame([['event' => 'austral.entity_manager.update', 'method' => 'update']], $container->getDefinition(PasswordListener::class)->getTag('kernel.event_listener'));
    self::assertSame([['command' => 'austral:security:user']], $container->getDefinition(UserCommand::class)->getTag('console.command'));
    self::assertFalse($container->getAlias('austral.security.password.listener')->isPublic());
  }

  public function testTheParameters(): void
  {
    $container = $this->load();

    self::assertSame(User::class, $container->getParameter('austral.entity.user.class'));
    self::assertSame(Role::class, $container->getParameter('austral.entity.role.class'));
    self::assertSame(Group::class, $container->getParameter('austral.entity.group.class'));
    self::assertSame(900, $container->getParameter('austral_security.password_reset.lifetime'));
    self::assertSame(30, $container->getParameter('austral_security.password_reset.request_interval'));
    self::assertTrue($container->hasParameter('austral_entity_file.uploads.security_bundle'));
  }

  public function testThePublicServices(): void
  {
    $kit = new SecurityKit();
    $container = $this->load(['login_link' => ['firewall' => 'austral_admin']]);
    $container->register('security.authenticator.login_link_handler.austral_admin', FakeLoginLinkHandler::class);
    $container->compile();
    $container->set('doctrine.orm.entity_manager', $kit->doctrine);
    $container->set('event_dispatcher', $kit->dispatcher);
    $container->set('security.user_password_hasher', $kit->hasher);

    self::assertInstanceOf(UserEntityManager::class, $container->get('austral.entity_manager.user'));
    self::assertSame(User::class, $container->get('austral.entity_manager.user')->getClass());
    self::assertInstanceOf(RoleEntityManager::class, $container->get('austral.entity_manager.role'));
    self::assertInstanceOf(GroupEntityManager::class, $container->get('austral.entity_manager.group'));
    self::assertInstanceOf(UserProvider::class, $container->get('austral.security.user_provider'));
    self::assertInstanceOf(UserChecker::class, $container->get('austral.security.user_checker'));
    self::assertInstanceOf(PasswordUpdater::class, $container->get('austral.security.password_updater'));
    $passwordReset = $container->get('austral.security.password_reset');
    self::assertInstanceOf(PasswordResetManager::class, $passwordReset);
    self::assertSame(900, $passwordReset->getLifetime());
    $loginLink = $container->get('austral.security.login_link');
    self::assertInstanceOf(LoginLinkRequester::class, $loginLink);
    self::assertTrue($loginLink->isEnabled());
  }

  public function testNoFirewallNoLoginLink(): void
  {
    $container = $this->load();
    $container->register('security.authenticator.login_link_handler.austral_admin', FakeLoginLinkHandler::class);
    $container->compile();
    $container->set('doctrine.orm.entity_manager', new SecurityKit()->doctrine);
    $container->set('event_dispatcher', new SecurityKit()->dispatcher);

    $loginLink = $container->get('austral.security.login_link');
    self::assertInstanceOf(LoginLinkRequester::class, $loginLink);
    self::assertFalse($loginLink->isEnabled(), 'The bundle does not guess the firewall of the application.');
  }

  public function testTheFirewallHasNoLoginLink(): void
  {
    $container = $this->load(['login_link' => ['firewall' => 'main']]);
    $container->compile();
    $container->set('doctrine.orm.entity_manager', new SecurityKit()->doctrine);
    $container->set('event_dispatcher', new SecurityKit()->dispatcher);

    $loginLink = $container->get('austral.security.login_link');
    self::assertInstanceOf(LoginLinkRequester::class, $loginLink);
    self::assertFalse($loginLink->isEnabled());
  }

  public function testTheInterfacesAreResolvedToTheEntitiesOfTheApplication(): void
  {
    $container = new ContainerBuilder();
    $container->registerExtension(new DoctrineExtensionStub());
    $container->prependExtensionConfig('austral_security', self::CONFIG);

    new AustralSecurityExtension()->prepend($container);

    self::assertSame([['orm' => ['resolve_target_entities' => [
      UserInterface::class => User::class,
      RoleInterface::class => Role::class,
      GroupInterface::class => Group::class,
    ]]]], $container->getExtensionConfig('doctrine'));
  }

  public function testNothingIsPrependedWithoutDoctrine(): void
  {
    $container = new ContainerBuilder();
    new AustralSecurityExtension()->prepend($container);

    self::assertSame([], $container->getExtensionConfig('doctrine'));
  }

  public function testTheBridgeWithAdminBundleIsLoadedWhenTheBundleIsEnabled(): void
  {
    self::assertFalse($this->load()->hasDefinition(UserAdmin::class));

    $container = $this->load([], ['AustralAdminBundle' => AustralAdminBundle::class]);
    foreach ([UserAdmin::class, AdminUserAdmin::class, GroupAdmin::class, RoleAdmin::class] as $id)
    {
      self::assertTrue($container->getDefinition($id)->hasTag('austral.admin'), $id);
    }
  }

  public function testTheModulesOfTheBridgeArePrepended(): void
  {
    $container = new ContainerBuilder();
    new AustralSecurityExtension()->prepend($container);
    self::assertSame([], $container->getExtensionConfig('austral_admin'), 'Nothing without admin-bundle.');

    $container->registerExtension(new AustralAdminExtension());
    new AustralSecurityExtension()->prepend($container);
    $modules = $container->getExtensionConfig('austral_admin')[0]['modules'] ?? [];
    self::assertSame(['admin-user', 'user', 'group', 'role'], array_keys($modules['security']['children'] ?? []));
    self::assertSame(UserAdmin::class, $modules['security']['children']['user']['class'] ?? null);
  }

  /**
   * @param array<string, mixed>        $config
   * @param array<string, class-string> $bundles
   */
  private function load(array $config = [], array $bundles = []): ContainerBuilder
  {
    $container = new ContainerBuilder();
    $container->setParameter('kernel.bundles', $bundles);
    foreach (['doctrine.orm.entity_manager', 'event_dispatcher', 'security.user_password_hasher'] as $id)
    {
      $container->setDefinition($id, new Definition()->setSynthetic(true)->setPublic(true));
    }
    new AustralSecurityExtension()->load([self::CONFIG, $config], $container);

    return $container;
  }
}
