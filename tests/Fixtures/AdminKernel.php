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

use Austral\AdminBundle\AustralAdminBundle;
use Austral\EntityBundle\AustralEntityBundle;
use Austral\EntityFileBundle\AustralEntityFileBundle;
use Austral\FilterBundle\AustralFilterBundle;
use Austral\FormBundle\AustralFormBundle;
use Austral\ListBundle\AustralListBundle;
use Austral\SecurityBundle\AustralSecurityBundle;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Group;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Role;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Austral\ToolsBundle\AustralToolsBundle;
use Composer\InstalledVersions;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Application of the functional tests of the bridge with admin-bundle: the admin on SQLite, with the modules of
 * security-bundle only.
 */
final class AdminKernel extends Kernel
{
  use MicroKernelTrait;

  /**
   * @param array<string, mixed> $adminConfig Merged into the configuration of austral_admin
   */
  public function __construct(string $environment = 'test', bool $debug = false, private readonly array $adminConfig = [])
  {
    parent::__construct($environment, $debug);
  }

  public function registerBundles(): iterable
  {
    yield new FrameworkBundle();
    yield new SecurityBundle();
    yield new TwigBundle();
    yield new DoctrineBundle();
    yield new AustralToolsBundle();
    yield new AustralEntityBundle();
    yield new AustralEntityFileBundle();
    yield new AustralFormBundle();
    yield new AustralListBundle();
    yield new AustralFilterBundle();
    yield new AustralSecurityBundle();
    yield new AustralAdminBundle();
  }

  public function getProjectDir(): string
  {
    return \dirname(__DIR__, 2);
  }

  public function getCacheDir(): string
  {
    return sys_get_temp_dir().'/austral-security-admin-tests/'.md5(serialize($this->adminConfig)).'/cache';
  }

  public function getLogDir(): string
  {
    return sys_get_temp_dir().'/austral-security-admin-tests/logs';
  }

  public function getDatabasePath(): string
  {
    return \dirname($this->getCacheDir()).'/test.sqlite';
  }

  protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
  {
    $loader->load(function (ContainerBuilder $container): void {
      $container->loadFromExtension('framework', [
        'secret' => 'test',
        'test' => true,
        'http_method_override' => false,
        'handle_all_throwables' => true,
        'php_errors' => ['log' => true],
        'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
        'csrf_protection' => true,
        'form' => true,
        'validation' => ['email_validation_mode' => 'html5'],
        'translator' => ['default_path' => '%kernel.project_dir%/translations', 'fallbacks' => ['en']],
        'default_locale' => 'en',
        'property_access' => true,
        'router' => ['utf8' => true],
      ]);
      $container->loadFromExtension('twig', ['strict_variables' => true]);
      $container->loadFromExtension('doctrine', [
        'dbal' => ['driver' => 'pdo_sqlite', 'path' => $this->getDatabasePath()],
        'orm' => self::nativeLazyObjects() + [
          'mappings' => ['Test' => ['type' => 'attribute', 'dir' => __DIR__.'/Entity', 'prefix' => 'Austral\SecurityBundle\Tests\Fixtures\Entity', 'is_bundle' => false]],
          'resolve_target_entities' => [UserInterface::class => User::class, RoleInterface::class => Role::class, GroupInterface::class => Group::class],
        ],
      ]);
      $container->loadFromExtension('austral_security', [
        'entities' => ['user' => User::class, 'role' => Role::class, 'group' => Group::class],
      ]);
      $container->loadFromExtension('security', [
        'role_hierarchy' => ['ROLE_ROOT' => ['ROLE_ADMIN_ACCESS', 'ROLE_USER_ACCESS']],
        'password_hashers' => [UserInterface::class => 'plaintext'],
        'providers' => ['austral_security_user' => ['id' => 'austral.security.user_provider']],
        'firewalls' => [
          'austral_admin' => [
            'pattern' => '^%austral_admin.path%',
            'lazy' => true,
            'provider' => 'austral_security_user',
            'user_checker' => 'austral.security.user_checker',
            'form_login' => [
              'login_path' => 'austral_admin_security_login',
              'check_path' => 'austral_admin_security_login',
              'default_target_path' => 'austral_admin_index',
              'enable_csrf' => true,
            ],
            'logout' => ['path' => 'austral_admin_security_logout', 'target' => 'austral_admin_security_login'],
          ],
        ],
        'access_control' => [
          ['path' => '^%austral_admin.path%/(login|logout|password)', 'roles' => 'PUBLIC_ACCESS'],
          ['path' => '^%austral_admin.path%', 'roles' => 'ROLE_ADMIN_ACCESS'],
        ],
      ]);
      $container->loadFromExtension('austral_admin', $this->adminConfig);

      $container->register('logger', NullLogger::class);
    });
  }

  /**
   * Native lazy objects: an option of DoctrineBundle 2.x, always on (option deprecated) from 3.1.
   *
   * @return array<string, bool>
   */
  private static function nativeLazyObjects(): array
  {
    $version = InstalledVersions::getVersion('doctrine/doctrine-bundle') ?? '0';

    return version_compare($version, '3.1', '>=') ? [] : ['enable_native_lazy_objects' => true];
  }

  protected function configureRoutes(RoutingConfigurator $routes): void
  {
    $routes->import('.', 'austral_admin');
  }
}
