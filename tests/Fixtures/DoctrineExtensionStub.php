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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * The extension "doctrine" is registered, as with the DoctrineBundle.
 */
final class DoctrineExtensionStub extends Extension
{
  public function load(array $configs, ContainerBuilder $container): void
  {
  }

  public function getAlias(): string
  {
    return 'doctrine';
  }
}
