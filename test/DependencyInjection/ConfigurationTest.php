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

namespace Janalis\Doctrineviz\Test\DependencyInjection;

use Janalis\Doctrineviz\DependencyInjection\Configuration;
use Janalis\Doctrineviz\Test\DoctrinevizTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\DependencyInjection\Configuration
 */
class ConfigurationTest extends DoctrinevizTestCase
{
    /**
     * Test constructor.
     *
     * @covers ::__construct
     * @group dependecy_injection
     */
    public function testConstructor()
    {
        $root = 'foo';
        $config = new Configuration($root);
        $this->assertEquals($root, $config->getRoot());
    }

    /**
     * Test get root.
     *
     * @covers ::getRoot
     * @group dependecy_injection
     */
    public function testGetRoot()
    {
        $root = 'foo';
        $config = new Configuration($root);
        $this->assertEquals($root, $config->getRoot());
    }

    /**
     * Test get config tree builder.
     *
     * @covers ::getConfigTreeBuilder
     * @group dependecy_injection
     */
    public function testGetConfigTreeBuilder()
    {
        $config = new Configuration('foo');
        $this->assertInstanceOf(TreeBuilder::class, $config->getConfigTreeBuilder());
    }
}
