<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\DependencyInjection;

use Austral\AdminBundle\DependencyInjection\AdminModulesPrepender;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\LoginLink\LoginLinkRequester;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Austral Security Extension.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class AustralSecurityExtension extends Extension implements PrependExtensionInterface
{
  /**
   * The interfaces of the associations are resolved to the classes of the application.
   *
   * @param ContainerBuilder $container
   *
   * @return void
   */
  public function prepend(ContainerBuilder $container): void
  {
    // Bridge: the modules of the users, groups and roles in admin-bundle, when it is enabled.
    if (class_exists(AdminModulesPrepender::class))
    {
      AdminModulesPrepender::prepend($container, __DIR__.'/../Bridge/Admin/austral_admin.yaml');
    }
    if (!$container->hasExtension('doctrine'))
    {
      return;
    }
    $config = $this->processConfiguration(new Configuration(), $container->getExtensionConfig($this->getAlias()));
    $container->prependExtensionConfig('doctrine', [
      'orm' => [
        'resolve_target_entities' => [
          UserInterface::class => $config['entities']['user'],
          RoleInterface::class => $config['entities']['role'],
          GroupInterface::class => $config['entities']['group'],
        ],
      ],
    ]);
  }

  /**
   * @param array<array-key, mixed> $configs
   * @param ContainerBuilder        $container
   *
   * @return void
   */
  public function load(array $configs, ContainerBuilder $container): void
  {
    $config = $this->processConfiguration(new Configuration(), $configs);
    $container->setParameter('austral.entity.user.class', $config['entities']['user']);
    $container->setParameter('austral.entity.role.class', $config['entities']['role']);
    $container->setParameter('austral.entity.group.class', $config['entities']['group']);
    $container->setParameter('austral_security.password_reset.lifetime', $config['password_reset']['lifetime']);
    $container->setParameter('austral_security.password_reset.request_interval', $config['password_reset']['request_interval']);

    $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('services.yaml');
    $loader->load('austral_entity_file.yaml');
    if (self::isBundleEnabled($container, 'AustralAdminBundle'))
    {
      $loader->load('bridge/admin.yaml');
    }

    // The firewall is in the security.yaml of the application; its login_link handler exists only when it has
    // "login_link".
    $firewall = $config['login_link']['firewall'];
    if (\is_string($firewall) && '' !== $firewall)
    {
      $container->getDefinition(LoginLinkRequester::class)
        ->setArgument('$loginLinkHandler', new Reference("security.authenticator.login_link_handler.{$firewall}", ContainerInterface::NULL_ON_INVALID_REFERENCE))
        ->setArgument('$firewall', $firewall);
    }
  }

  /**
   * @param ContainerBuilder $container
   * @param string           $name
   *
   * @return bool
   */
  private static function isBundleEnabled(ContainerBuilder $container, string $name): bool
  {
    $bundles = $container->hasParameter('kernel.bundles') ? $container->getParameter('kernel.bundles') : [];

    return \is_array($bundles) && isset($bundles[$name]);
  }

  /**
   * @return string
   */
  public function getNamespace(): string
  {
    return 'https://austral.dev/schema/dic/austral_security';
  }
}
