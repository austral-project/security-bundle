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

use Austral\SecurityBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
  public function testTheDefaults(): void
  {
    self::assertSame([
      'entities' => [
        'user' => 'App\Entity\Austral\SecurityBundle\User',
        'role' => 'App\Entity\Austral\SecurityBundle\Role',
        'group' => 'App\Entity\Austral\SecurityBundle\Group',
      ],
      'password_reset' => ['lifetime' => 3600, 'request_interval' => 60],
      'login_link' => ['firewall' => null],
    ], $this->process([]));
  }

  public function testTheValues(): void
  {
    $config = $this->process([
      'password_reset' => ['lifetime' => 900, 'request_interval' => 0],
      'login_link' => ['firewall' => 'admin'],
    ]);

    self::assertSame(['lifetime' => 900, 'request_interval' => 0], $config['password_reset']);
    self::assertSame(['firewall' => 'admin'], $config['login_link']);
  }

  public function testALifetimeOfAtLeastAMinute(): void
  {
    $this->expectException(InvalidConfigurationException::class);
    $this->process(['password_reset' => ['lifetime' => 59]]);
  }

  /**
   * @param array<string, mixed> $config
   *
   * @return array<string, mixed>
   */
  private function process(array $config): array
  {
    return new Processor()->processConfiguration(new Configuration(), [$config]);
  }
}
