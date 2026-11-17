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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Smoke test: the bundle can be loaded. To be completed with real tests.
 */
final class BundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        self::assertInstanceOf(BundleInterface::class, new AustralSecurityBundle());
    }
}
