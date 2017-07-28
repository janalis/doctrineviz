<?php

/*
 * This file is part of the doctrineviz package
 *
 * Copyright (c) 2017 Pierre Hennequart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Pierre Hennequart <pierre@janalis.com>
 */

declare(strict_types=1);

namespace Janalis\Doctrineviz\Test;

use Janalis\Doctrineviz\DependencyInjection\DoctrinevizBundleExtension;
use Janalis\Doctrineviz\DoctrinevizBundle;

/**
 * Doctrineviz bundle test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\DoctrinevizBundle
 */
class DoctrinevizBundleTest extends DoctrinevizTestCase
{
    /**
     * Test get container extension.
     *
     * @covers ::getContainerExtension
     * @group bundle
     */
    public function testGetContainerExtension()
    {
        $bundle = new DoctrinevizBundle();
        $extension = $bundle->getContainerExtension();
        $this->assertInstanceOf(DoctrinevizBundleExtension::class, $extension);
    }
}
