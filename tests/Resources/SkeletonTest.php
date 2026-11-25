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

namespace Austral\SecurityBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * The files and the configuration given to the applications are valid.
 */
final class SkeletonTest extends TestCase
{
  private const string RESOURCES = __DIR__.'/../../src';

  /**
   * The security.yaml of the README: the firewall belongs to the application.
   */
  public function testTheSecurityConfigurationOfTheReadme(): void
  {
    $container = new ContainerBuilder();
    $bundle = new SecurityBundle();
    $extension = $bundle->getContainerExtension();
    self::assertInstanceOf(SecurityExtension::class, $extension);
    $container->registerExtension($extension);
    $bundle->build($container);
    $configuration = $extension->getConfiguration([], $container);
    self::assertInstanceOf(ConfigurationInterface::class, $configuration);

    $config = new Processor()->processConfiguration($configuration, [Yaml::parse(<<<'YAML'
      role_hierarchy:
        ROLE_ROOT: [ ROLE_ADMIN_ACCESS, ROLE_USER_ACCESS ]
      password_hashers:
        App\Entity\Austral\SecurityBundle\User: 'auto'
      providers:
        austral_security_user:
          id: austral.security.user_provider
      firewalls:
        main:
          provider: austral_security_user
          user_checker: austral.security.user_checker
          form_login: ~
          login_link:
            check_route: app_login_link_check
            signature_properties: [ id, email, password, isActive ]
            lifetime: 600
            max_uses: 1
            check_post_only: true
      YAML)]);

    self::assertSame('austral.security.user_provider', $config['providers']['austral_security_user']['id']);
    self::assertSame('austral.security.user_checker', $config['firewalls']['main']['user_checker']);
    self::assertSame(600, $config['firewalls']['main']['login_link']['lifetime']);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function entities(): iterable
  {
    foreach (['User', 'Role', 'Group'] as $entity)
    {
      yield $entity => [self::RESOURCES."/Skeleton/Entity/{$entity}.php"];
    }
  }

  #[DataProvider('entities')]
  public function testTheEntitiesOfTheApplication(string $file): void
  {
    $code = (string) file_get_contents($file);

    self::assertStringStartsWith('<?php', $code);
    // Throws a \ParseError when the PHP is not valid.
    \PhpToken::tokenize($code, \TOKEN_PARSE);
    self::assertStringContainsString('namespace App\Entity\Austral\SecurityBundle;', $code);
  }
}
