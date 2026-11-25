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

namespace Austral\SecurityBundle\Tests;

use Austral\SecurityBundle\AustralSecurityBundle;
use Austral\SecurityBundle\DependencyInjection\AustralSecurityExtension;
use PHPUnit\Framework\TestCase;

final class BundleTest extends TestCase
{
  public function testTheExtensionOfTheBundle(): void
  {
    $extension = new AustralSecurityBundle()->getContainerExtension();

    self::assertInstanceOf(AustralSecurityExtension::class, $extension);
    self::assertSame('austral_security', $extension->getAlias());
  }
}
