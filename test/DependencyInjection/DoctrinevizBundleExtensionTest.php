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

namespace Janalis\Doctrineviz\Test\DependencyInjection;

use Janalis\Doctrineviz\DependencyInjection\DoctrinevizBundleExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Doctrineviz bundle extension test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\DependencyInjection\DoctrinevizBundleExtension
 */
class DoctrinevizBundleExtensionTest extends WebTestCase
{
    /**
     * Test prepend.
     *
     * @covers ::prepend
     * @group dependency_injection
     */
    public function testPrepend()
    {
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getParameter',
            ])
            ->getMock();
        $containerBuilder->expects($this->once())->method('getParameter')->will($this->returnValue([
            'DoctrineBundle' => [],
        ]));
        $extension = new DoctrinevizBundleExtension();
        $extension->prepend($containerBuilder);
    }

    /**
     * Test prepend: missing required bundle.
     *
     * @covers ::prepend
     * @group dependency_injection
     */
    public function testPrependMissingRequiredBundle()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DoctrineBundle must be registered in your application.');
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getParameter',
            ])
            ->getMock();
        $containerBuilder->expects($this->once())->method('getParameter')->will($this->returnValue([]));
        $extension = new DoctrinevizBundleExtension();
        $extension->prepend($containerBuilder);
    }

    /**
     * Test load.
     *
     * @covers ::load
     * @group dependency_injection
     */
    public function testLoad()
    {
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $extension = new DoctrinevizBundleExtension();
        $extension->load([], $containerBuilder);
    }
}
